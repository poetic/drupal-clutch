<?php

/**
 * @file
 * Contains \Drupal\clutch\Command\CreateCommand.
 */

namespace Drupal\clutch\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Command\Command;
use Drupal\Console\Style\DrupalStyle;
use Clutch\Command\BaseCommand;
use ZipArchive;

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
       ->setName('clutch')
      ->setDescription('This will generate your components folder')
      ->setDefinition(array(
        new InputOption('zip-file', 'z', InputOption::VALUE_REQUIRED, 'Name of the zip file.'),
        new InputOption('theme-name', 't', InputOption::VALUE_REQUIRED, 'Theme Name'),
        new InputOption('theme-description', 'd', InputOption::VALUE_REQUIRED, 'Theme description'),
      ));
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
      $zip->extractTo('html/');
      $zip->close();
      $output->writeln('<info>Starting Theme creation process</info>');
    } else {
      $output->writeln('<comment>Failed to open the archive!</comment>');
      return false;
    }
    $directory = "html/{$bundlezip}/";
    $htmlfiles = glob($directory . "*.html");
    $themeMachine = strtolower(str_replace(" ","_",$theme));
    $create = new BaseCommand;
    $create->Directory($theme,$bundlezip);
    $vars = array('{{themeName}}'=> $theme,'{{themeMachine}}'=> $themeMachine,'{{themeDescription}}'=> $themeDesc);
    $create->ThemeTemplates($theme, $vars);
    $create->Components($theme,$htmlfiles,'data-bundle','components');
    $create->Components($theme,$htmlfiles,'data-node','nodes');
    $create->Components($theme,$htmlfiles,'data-views-full-mode','views');
    $create->Components($theme,$htmlfiles,'data-views-teaser-mode','teaser');
    $create->deleteDirectory('html');
    $output->writeln('<info>Good Job your theme is ready!</info>');
  }
}