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
use Drupal\clutch\Form\ClutchAPIForm;
use Drupal\clutch\PageBuilder;
use Drupal\clutch\MenuBuilder;
use Drupal\clutch\BlockBuilder;

/**
 * Class CreateCommand.
 *
 * @package Drupal\clutch
 */
class PageCommand extends Command {
  /**
   * {@inheritdoc}
   */
  protected $moduleInstaller;
  protected function configure()
  {
    $this
      ->setName('clutch:sync')
      ->setDescription('This will generate your components folder')
      ->addOption(
        'theme',
        '',
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.theme.options.machine-name')
      )
      ;
  }
  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $io = new DrupalStyle($input, $output);

    $theme = $input->getOption('theme');
    if(!$theme){
      $helper = $this->getHelper('question');
      $question = new Question('<info>Please enter theme name:</info> <comment>[webflow]</comment> ', 'webflow');
      $theme = $helper->ask($input, $output, $question);
    }
    $output->writeln('<info>Oh Well now you can... Ummm TAKE A WALK?</info>');
    \Drupal::service('theme_handler')->refreshInfo();

    $themes  = \Drupal::service('theme_handler')->rebuildThemeData();
    $themesAvailable = [];
    $themesInstalled = [];
    $themesUnavailable = [];
    foreach ($theme as $themeName) {
      if (isset($themes[$themeName]) && $themes[$themeName]->status == 0) {
        $themesAvailable[] = $themes[$themeName]->info['name'];
      } elseif (isset($themes[$themeName]) && $themes[$themeName]->status == 1) {
        $themesInstalled[] = $themes[$themeName]->info['name'];
      } else {
        $themesUnavailable[] = $themeName;
      }
    }
    if (count($themesAvailable) > 0) {
      try {
        if ($themeHandler->install($theme)) {
          if (count($themesAvailable) > 1) {
            $io->info(
              sprintf(
                $this->trans('commands.theme.install.messages.themes-success'),
                implode(',', $themesAvailable)
              )
            );
          } else {
            if ($default) {
              // Set the default theme.
              $config->set('default', $theme[0])->save();
              $io->info(
                sprintf(
                  $this->trans('commands.theme.install.messages.theme-default-success'),
                  $themesAvailable[0]
                )
              );
            } else {
              $io->info(
                sprintf(
                  $this->trans('commands.theme.install.messages.theme-success'),
                  $themesAvailable[0]
                )
              );
            }
          }
        }
      } catch (UnmetDependenciesException $e) {
        $io->error(
          sprintf(
            $this->trans('commands.theme.install.messages.success'),
            $theme
          )
        );
        drupal_set_message($e->getTranslatedMessage($this->getStringTranslation(), $theme), 'error');
      }
    } elseif (empty($themesAvailable) && count($themesInstalled) > 0) {
      if (count($themesInstalled) > 1) {
        $io->info(
          sprintf(
            $this->trans('commands.theme.install.messages.themes-nothing'),
            implode(',', $themesInstalled)
          )
        );
      } else {
        $io->info(
          sprintf(
            $this->trans('commands.theme.install.messages.theme-nothing'),
            implode(',', $themesInstalled)
          )
        );
      }
    } else {
      if (count($themesUnavailable) > 1) {
        $io->error(
          sprintf(
            $this->trans('commands.theme.install.messages.themes-missing'),
            implode(',', $themesUnavailable)
          )
        );
      }
    }

    \Drupal::service('theme_installer')->install([$theme]);
    \Drupal::service('theme_handler')->setDefault($theme);
    
    $Root = getcwd().'/themes';
    $themeDir = "{$Root}/{$theme}";
    $blocks_dir = scandir($themeDir . '/blocks/');
    $bundles_from_theme_directory = array();
    foreach ($blocks_dir as $dir) {
      if (strpos($dir, '.') !== 0) {
        $bundles_from_theme_directory[] = str_replace('-', '_', $dir);
      }
    }

    $output->writeln('<info>Start building blocks...</info>');
    $BlockBuilder = new BlockBuilder();
    $BlockBuilder->createEntitiesFromTemplate($bundles_from_theme_directory, 'block_content');
    $output->writeln('<info>Finish building blocks</info>');
    $output->writeln('<info>Start building pages and add blocks...</info>');

    $PageBuilder = new PageBuilder();
    $PageBuilder->build($theme);
    $output->writeln('<info>Cluch Sync Complete.</info>');
  }
}
