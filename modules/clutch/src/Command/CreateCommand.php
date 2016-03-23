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

use Drupal\clutch\MenuBuilder;

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
            )
            ->addOption(
                'theme-description',
                '',
                InputOption::VALUE_REQUIRED,
                $this->trans('commands.generate.theme.options.module-path')
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
      $themeDesc = $input->getOption('theme-description');
      if(!$themeDesc){
        $helper = $this->getHelper('question');
        $question = new Question('<info>Please enter theme description:</info> <comment>[These is a webflow theme]</comment> ', 'These is a webflow theme');
        $themeDesc = $helper->ask($input, $output, $question);
      }
      $zip = new ZipArchive;
      if ($zip->open($withZip) === TRUE) {
        $zip->extractTo('temp/');
        $zip->close();
        $output->writeln('<info>Starting Theme creation process</info>');
      } else {
        $output->writeln('<comment>Failed to open the archive!</comment>');
        return false;
      }
      $directory = "temp/{$bundlezip}/";
      $htmlfiles = glob($directory . "*.html");
      $themeMachine = strtolower(str_replace(" ","_",$theme));
      $Root = getcwd().'/themes';
      $themeDir = "{$Root}/{$theme}";

      $create = new ClutchCli;
      
      $create->Components($themeDir,$theme,$htmlfiles,'data-component','components');
      $create->Components($themeDir,$theme,$htmlfiles,'data-node','nodes');
      $create->Components($themeDir,$theme,$htmlfiles,'data-view','views');
      $create->Components($themeDir,$theme,$htmlfiles,'data-views-teaser','teaser');
      $create->Components($themeDir,$theme,$htmlfiles,'data-menu','menus');
      $create->Directory($Root,$themeDir,$theme,$bundlezip);
      $vars = array('{{themeName}}'=> $theme,'{{themeMachine}}'=> $themeMachine,'{{themeDescription}}'=> $themeDesc);
      $create->ThemeTemplates($themeDir,$theme, $vars);
      

      $create->deleteDirectory('temp');

      $output->writeln('<info>You been Clutched!</info>');
      \Drupal::service('theme_handler')->rebuildThemeData();
      \Drupal::service('theme_handler')->reset();
      \Drupal::service('theme_handler')->refreshInfo();
      \Drupal::service('theme_handler')->listInfo();
     // $this->getChain()->addCommand('cache:rebuild');
      // $this->getChain()->addCommand('clutch:sync');
      // $output->writeln('<comment>'.$theme.' is now installed and set as default.</comment>');  

    }

    
}
