<?php

/**
 * @file
 * Contains \Drupal\clutch\Command\CreateCommand.
 */

namespace Drupal\clutch\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Drupal\Console\Command\Command;
use Drupal\Console\Style\DrupalStyle;
use Drupal\clutch\ClutchCli;
use ZipArchive;
use Symfony\Component\Console\Helper\ProgressBar;
use Drupal\clutch\MenuBuilder;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Finder\Finder;

/**
 * Class CreateCommand.
 *
 * @package Drupal\clutch
 */
class CreateCommand extends Command {
  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('clutch:create')
      ->setDescription('This will generate your components folder')
      ->addOption(
        'zip-file',
        '-z',
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.theme.options.module')
      )
      ->addOption(
        'theme-name',
        '-theme',
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.theme.options.machine-name')
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $io = new DrupalStyle($input, $output);
    $zipFile = $input->getOption('zip-file');
    if(!$zipFile){
      $helper = $this->getHelper('question');
      $question = new Question('<info>Please enter the name of the zip file:</info> <comment>[webflow]</comment> ', 'webflow');
      $zipFile = $helper->ask($input, $output, $question);
    }
    $withZip = $zipFile. ".zip";
    // Theme Name
    $theme = $input->getOption('theme-name');
    if(!$theme){
      $helper = $this->getHelper('question');
      $question = new Question('<info>Please enter theme name:</info> <comment>[webflow]</comment> ', 'webflow');
      $theme = $helper->ask($input, $output, $question);
    }
    
    $themeDesc = 'These is a webflow theme';
    
    $tempPath = getcwd().'/temp';

    $zip = new ZipArchive;
    if ($zip->open($withZip) === TRUE) {
      $zip->extractTo($tempPath);
      $zip->close();
      $output->writeln('<info>Starting Theme creation process</info>');
    } else {
      $output->writeln('<comment>Failed to open the archive!</comment>');
      return false;
    }

    $extract_webflow_dir = "$tempPath/$zipFile/";

    $htmlfiles = array();

    $finder = new Finder();
    $finder->files()->name('*.html')->in($extract_webflow_dir);
    foreach ($finder as $file) {
      $htmlfiles[] = $file->getRealpath();
    }
    $theme_machine_name = strtolower(str_replace(" ","_",$theme));
    $root = getcwd();
    $themeDir = "$root/themes/$theme_machine_name/";
    if(!file_exists($themeDir)) {
      mkdir($themeDir, 0777);
    }
    $clutchCLI = new ClutchCli();
    $folders_to_copy = array('blocks','nodes', 'menus', 'forms', 'css', 'js', 'fonts', 'images');
    $clutchCLI->traverseFiles($extract_webflow_dir, $themeDir, $theme_machine_name, $htmlfiles);
    $clutchCLI->copyWebflowFilesToTheme($extract_webflow_dir, $themeDir, $theme_machine_name, $folders_to_copy, $output);
    $theme_vars = array('{{themeName}}'=> $theme,'{{themeMachine}}'=> $theme_machine_name,'{{themeDescription}}'=> $themeDesc);
    $clutchCLI->generateThemeTemplates($themeDir, $theme_vars);
    $clutchCLI->deleteDirectory($tempPath);
    \Drupal::service('theme_handler')->rebuildThemeData();
    \Drupal::service('theme_handler')->reset();
    \Drupal::service('theme_handler')->refreshInfo();
    \Drupal::service('theme_handler')->listInfo();  
    $output->writeln('<comment>'. "\r\n" .'Your theme '.$theme.' is now created.</comment>');      
  }
}