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
                '',
                InputOption::VALUE_REQUIRED,
                $this->trans('commands.generate.theme.options.module')
            )
            ->addOption(
                'theme-name',
                '',
                InputOption::VALUE_REQUIRED,
                $this->trans('commands.generate.theme.options.machine-name')
            );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $io = new DrupalStyle($input, $output);
     $bundlezip = $input->getOption('zip-file');
      if(!$bundlezip){
        $helper = $this->getHelper('question');
        $question = new Question('<info>Please enter the name of the zip file:</info> <comment>[webflow]</comment> ', 'webflow');
        $bundlezip = $helper->ask($input, $output, $question);
      }
      $withZip = $bundlezip. ".zip";
      // Theme Name
      $theme = $input->getOption('theme-name');
      if(!$theme){
        $helper = $this->getHelper('question');
        $question = new Question('<info>Please enter theme name:</info> <comment>[webflow]</comment> ', 'webflow');
        $theme = $helper->ask($input, $output, $question);
      }
      // Theme Description
      // $themeDesc = $input->getOption('theme-description');
      // if(!$themeDesc){
      //   $helper = $this->getHelper('question');
      //   $question = new Question('<info>Please enter theme description:</info> <comment>[These is a webflow theme]</comment> ', 'These is a webflow theme');
      //   $themeDesc = $helper->ask($input, $output, $question);
      // }
      $path = getcwd().'/temp';
      $zip = new ZipArchive;
      if ($zip->open($withZip) === TRUE) {
        $zip->extractTo($path);
        $zip->close();
        $output->writeln('<info>Starting Theme creation process</info>');
      } else {
        $output->writeln('<comment>Failed to open the archive!</comment>');
        return false;
      }
      $directory = "{$path}/{$bundlezip}/";
      $htmlfiles = glob($directory . "*.html");
      $themeMachine = strtolower(str_replace(" ","_",$theme));
      $Root = getcwd().'/themes';
      $themeDir = "{$Root}/{$theme}";
      $create = new ClutchCli;
      $output = new ConsoleOutput();
      $output->setFormatter(new OutputFormatter(true));
      $rows = 8;
      $progressBar = new ProgressBar($output, $rows);
      $progressBar->setBarCharacter('<fg=magenta>=</>');
      $progressBar->setProgressCharacter("\xF0\x9F\x8F\x83");

      for ($i = 0; $i<$rows; $i++) {
        $create->Components($themeDir,$theme,$htmlfiles,'data-component','components');
        $create->Components($themeDir,$theme,$htmlfiles,'data-node','nodes');
        $create->Components($themeDir,$theme,$htmlfiles,'data-view','views');
        $create->Components($themeDir,$theme,$htmlfiles,'data-views-teaser','teaser');
        $create->Components($themeDir,$theme,$htmlfiles,'data-menu','menus');
        $progressBar->advance();
        $create->Directory($path,$Root,$themeDir,$theme,$bundlezip);
        $progressBar->advance();
        $vars = array('{{themeName}}'=> $theme,'{{themeMachine}}'=> $themeMachine,'{{themeDescription}}'=> $themeDesc);
        $progressBar->advance();
        $create->ThemeTemplates($themeDir,$theme, $vars);
        $progressBar->advance();
        $create->deleteDirectory($path);
        $progressBar->advance();
        \Drupal::service('theme_handler')->rebuildThemeData();
        $progressBar->advance();
        \Drupal::service('theme_handler')->reset();
        $progressBar->advance();
        // $output->writeln('<comment>'.$theme.' is now installed and set as default.</comment>');
      }
              \Drupal::service('theme_handler')->refreshInfo();
        \Drupal::service('theme_handler')->listInfo();
        $this->getChain()->addCommand('cache:rebuild', ['cache' => 'all']);
        $this->getChain()->addCommand('clutch:sync', ['theme' => 'asd']);
      $progressBar->finish();
      $output->writeln('');
  }
}
