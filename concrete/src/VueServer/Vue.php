<?php

namespace Concrete\Core\VueServer;

use Concrete\Core\VueServer\Engine as VueEngine;

class Vue {
  public static $instance = null;

  public function __construct() {
    if (!isset(Vue::$instance)) {
      Vue::$instance = new VueEngine();

      if (!file_exists($this->getCacheFolder())) {
        @mkdir($this->getCacheFolder());
      }

      Vue::$instance->setCacheDirectory($this->getCacheFolder());
    }

  }

  private function attributesFromData(&$data) {
    $attrs = [];

    foreach ($data as $key => $value) {

      // Reactive attribute
      if ($key[0] == ':') {
        array_push($attrs, $key.'="'.$value.'"');

        unset($data[$key]); $data[substr($key, 1)] = $value;
      }

      // Static attribute
      if ($key[0] == '!') {
        array_push($attrs, substr($key, 1).'="'.$value.'"');

        unset($data[$key]); $data[substr($key, 1)] = $value;
      }

    }

    return $attrs;
  }

  public function renderComponent($component, $data = [], $pkgHandle = null) {
    $dir   = $pkgHandle === null ? $this->getComponentsFolder() : DIR_PACKAGES.'/'.$pkgHandle.'/components';
    $file  = $dir.'/'.$component.'.php';
    $attrs = $this->attributesFromData($data);

    Vue::$instance->setComponentDirectory($dir);

    $html = Vue::$instance->renderComponent(
      $component, $this->prepareData(@file_get_contents($file), $data)
    );

    array_push($attrs, 'vue='.$component);
    array_push($attrs, 'data-server-rendered="true"');

    $html = preg_replace('/<[^ ]+/', '$0 '.implode(' ', $attrs), $html, 1);
    $html = preg_replace('/\s\s+/', '', $html);
    $html = trim(str_replace(' class=""', '', $html));

    return $html;
  }

  public function renderHtml($html, $data = []) {
    return Vue::$instance->renderHtml($html, $this->prepareData($html, $data));
  }

  public function getCacheFolder() {
    return \Config::get('concrete.cache.directory').'/vue-render';
  }

  public function getComponentsFolder() {
    return DIR_BASE_CORE.'/components';
  }

  public static function renderer() {
    return new Vue();
  }

  private function takeJsData($html) {
    preg_match_all('/data:.*return([^;]*)/ms', $html, $jsData);

    if (isset($jsData[1])) {
      foreach ($jsData[1] as $jsd) {
        $jsd = preg_replace('/[a-zA-Z_0-9][^,:]+: *this\.[^,}]+/ims','',$jsd);
        $jsd = preg_replace('/\n/ims','',                               $jsd);
        $jsd = preg_replace('/, *}/ims','}',                            $jsd);
        $jsd = preg_replace('/([^:{}, ]+):/ims','"$1":',                $jsd);
        $jsd = preg_replace('/\'/ims','"',                              $jsd);
        $jsd = preg_replace('/, *(, *)+/ims','',                        $jsd);
        $jsd = preg_replace('/{ *,/ims','{',                            $jsd);

        return json_decode($jsd, true);
      }
    }

    return [];
  }

  private function prepareData($html, &$data) {
    preg_match_all('/{{ ([^}]+)}}/m', $html, $requiredData);

    $data = array_merge($this->takeJsData($html), $data);

    foreach ($requiredData[1] as $rd) {
      $rd       = trim($rd);
      $nesting  = explode('.', $rd);
      $deepness = count($nesting);
      $level    = &$data;

      for ($i = 0; $i < $deepness; $i++) {
        $key = $nesting[$i];

        if (!isset($level[$key])) {
          $level[$key] = $i < $deepness - 1 ? [] : '';
          $level       = &$level[$nesting[$i]];
        }

      }

    }

    return $data;
  }
}
