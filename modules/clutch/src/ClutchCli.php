<?php

/**
 * @file
 * Contains \Drupal\clutch\ClutchCli.
 */

namespace Drupal\clutch;

use DOMDocument;
use DOMXPath;

/**
 * Class ClutchCli.
 *
 * @package Drupal\clutch\Controller
 */
class ClutchCli {

  /**
   * Move files from zip to theme
   *
   * @param $src, $dst
   *   $src files from the zipfile
   *   $dst where the files are goint to
   *
   * @return
   *   render files to new location
   */
  function recurse_copy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                  $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

  /**
   * Replace template variables
   * with new theme content
   *
   * @param $string, $vars
   *   replace keys with actual content
   *
   * @return
   *   new template with theme name
   */
  function replace_tags($string, $vars){
      return str_replace(array_keys($vars), $vars, $string);
  }

  function deleteDirectory($dirPath) {
      if (is_dir($dirPath)) {
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
          if ($object != "." && $object !="..") {
            if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
              $this->deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
            } else {
              unlink($dirPath . DIRECTORY_SEPARATOR . $object);
            }
          }
        }
        reset($objects);
        rmdir($dirPath);
      }
  }

  function Directory($path,$Root,$themeDir,$theme,$bundlezip){
        $cssDir = "{$path}/{$bundlezip}/css";
        $jsDir = "{$path}/{$bundlezip}/js";
        $fontDir = "{$path}/{$bundlezip}/fonts";
        $imgDir = "{$path}/{$bundlezip}/images";
        $themecss = "{$Root}/{$theme}/css";
        $themejs = "{$Root}/{$theme}/js";
        $themefont = "{$Root}/{$theme}/fonts";
        $themeimg = "{$Root}/{$theme}/images";
        $tempInfo = __DIR__.'/../template';

        // Move files from zip to new theme.
        if(!$cssDir){
          $output->writeln('<comment>Failed to find CSS folder. make sure you are using the webflow zip</comment>');
          return false;
        }else{
            $css = opendir($cssDir);
          while(false !== ( $file = readdir($css)) ) {
            if ( substr($file, -12) == '.webflow.css'){
              rename( $cssDir.'/'.$file, $cssDir.'/'.$theme.substr($file, -12));
            }
          }
        // Move files from zip rename with theme name.
        $this->recurse_copy($cssDir,$themecss);
        }
        if(!$jsDir){
          $output->writeln('<comment>Failed to find JS folder. make sure you are using the webflow zip</comment>');
          return false;
        }else{
          $this->recurse_copy($jsDir,$themejs);
        }
        if(!$imgDir){
          $output->writeln('<comment>Failed to find Images folder. make sure you are using the webflow zip</comment>');
          return false;
        }else{
          $this->recurse_copy($imgDir,$themeimg);
        }

        $this->recurse_copy($tempInfo,$themeDir);
        rename($themeDir.'/info.yml',$themeDir.'/'.$theme.'.info.yml');
        rename($themeDir.'/libraries.yml',$themeDir.'/'.$theme.'.libraries.yml');
        rename($themeDir.'/template.theme',$themeDir.'/'.$theme.'.theme');
    }

    function ThemeTemplates($themeDir,$theme, $vars){
      $template = file_get_contents($themeDir.'/'.$theme.'.info.yml', true);
      $infoYML = $this->replace_tags($template, $vars);
      file_put_contents($themeDir.'/'.$theme.'.info.yml', $infoYML);

      $template = file_get_contents($themeDir.'/'.$theme.'.libraries.yml', true);
      $infoYML = $this->replace_tags($template, $vars);
      file_put_contents($themeDir.'/'.$theme.'.libraries.yml', $infoYML);

      $template = file_get_contents($themeDir.'/'.$theme.'.theme', true);
      $infoYML = $this->replace_tags($template, $vars);
      file_put_contents($themeDir.'/'.$theme.'.theme', $infoYML);
    }

    function Components($themeDir,$theme,$htmlfiles,$dataBundle,$bundle){
        $files = array();
            foreach($htmlfiles as &$file){
                $bundle_file_name = basename($file,".html");
                // echo $bundle_file_name."\r\n";
                $html = file_get_contents($file);
                $extracted_info = array(); //array to save all the info in just one array [data-component, data-field, div]
                //Extract data-component and store it to $bundle_names
                $data_bundles = explode($dataBundle.'="', $html);
                $bundle_names = array();
                  foreach ($data_bundles as &$data_bundle) {
                    $data_bundle = substr($data_bundle, 0, strpos($data_bundle, '"'));
                    array_push($bundle_names, $data_bundle);
                  }
                $doc = new DOMDocument;
                @$doc->loadHTML($html);
                $xpath = new DOMXPath($doc);
                  for ($i = 1; $i < count($bundle_names); $i++) { //Search for each data-component found
                    // echo $bundle_names[$i];
                    $result = '';
                    $query = '//*[@'.$dataBundle.'="' . $bundle_names[$i] . '"]/node()/..';
                    $node = $xpath->evaluate($query);
                      foreach ($node as $childNode) {
                        $result .= $doc->saveHtml($childNode); //store the div block to result line by line;
                      }
                    $bundle_names[$i] =  str_replace('_', '-', $bundle_names[$i]);
                    $temp = array($bundle_names[$i], $result);
                    array_push($extracted_info, $temp);
                  }
                $extracted_node = array(); //array to save all the info in just one array [data-component, data-field, div]
                //Extract data-component and store it to $bundle_names
                //Generate files
                $html_filename = basename($file);
                $html_filename = str_replace('.html', '', $html_filename);
                $theme_components = $themeDir."/".$bundle."/";
                $yamls = $themeDir."/";
                // var_dump($bundle_names);
                if (!file_exists($theme_components)) {
                mkdir($theme_components, 0777, true);
                }
                if($bundle == 'components'){
                  $page = $yamls.$bundle.'.yml';
                  if(0 < count($bundle_names)){
                    $pageBundle = $bundle_file_name . ":\r\n  ";
                    $pageBundle .= $bundle.':' . "\r\n      ";
                    for ($j = 1; $j < count($bundle_names); $j++) {
                      $bundle_names[$j] = str_replace('-', '_', $bundle_names[$j]);
                    $pageBundle .= '- ' . $bundle_names[$j] . '' . "\r\n      ";
                    }
                    $pageBundle .= '' . "\r\n";
                  }
                file_put_contents($page, $pageBundle, FILE_APPEND);
                }
                

                foreach ($extracted_info as &$info) {
                  if (!file_exists($theme_components . $info[0] )) {
                   mkdir($theme_components . $info[0] , 0777, true);
                  }
                  $filename = $theme_components . $info[0] . '/' . $info[0] . '.html.twig';
                  $created = $bundle.'/'.$info[0] . '/' . $info[0] . '.html.twig';
                  file_put_contents($filename, $info[1]);
                  // $output->writeln('<comment>'.$filename.' </comment>');
                  echo $created. "\r\n";
                }
            }
    }
}
