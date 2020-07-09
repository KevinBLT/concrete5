<?php
namespace Concrete\Core\Asset;

use Concrete\Core\Html\Object\HeadLink;
use Config;

class VueAppAsset extends Asset
{
    /**
     * @var bool
     */
    protected $assetSupportsMinification = true;

    /**
     * @var bool
     */
    protected $assetSupportsCombination = true;

    /**
     * The default selector of vue elements.
     *
     * @var string
     */
    protected $selector = '[vue]';

    /**
     * Set the selector of vue elements.
     *
     * @param string $selector
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
    }

    /**
     * Get the selector of vue elements.
     *
     * @return string
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @return string
     */
    public function getAssetDefaultPosition()
    {
        return Asset::ASSET_POSITION_FOOTER;
    }

    /**
     * @return bool
     */
    public function isAssetLocal()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Asset\AssetInterface::register()
     */
    public function register($filename, $args, $pkg = false)
    {
        parent::register($filename, $args, $pkg);

        if (!empty($args['selector'])) {
            $this->setSelector($args['selector']);
        }
    }

    /**
     * @return string|null
     */
    public function getAssetContents()
    {
        return '
          var vues       = document.querySelectorAll(\''.$this->getSelector().'\'),
              isEditMode = location.href.indexOf(\'ctask=check-out\') > -1;

          for (var iv = 0, vue, component, attributes; iv < vues.length; iv++) {
            component  = vues[iv].getAttribute(\'vue\') || null;
            attributes = [];

            if (isEditMode) {
              vues[iv].textContent = vues[iv].outerHTML; continue;
            }

            vues[iv].removeAttribute(\'vue\');

            if (component) {
              for (var i = 0, attr; i < vues[iv].attributes.length; i++) {
                attr = vues[iv].attributes[i];

                if (attr.name == \'data-server-rendered\') continue;

                attributes.push(attr.name + \'="\' + attr.value + \'"\')
              }

              component = \'<\' + component + \' \' + attributes.join(\' \') + \'></\' + component + \'>\';
            }

            vue = new Vue({
              el       : vues[iv],
              template : component ? component : null,
              data     : Object.assign(
                window.ccm_vue_data || {fu : 9}, '.json_encode($this->getAssetURL()).
              ')
            });

          }
        ';
    }

    /**
     * @return string
     */
    public function getAssetType()
    {
        return 'vue-app';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "</script><script data-type=\"".$this->getAssetType()."\" type=\"text/javascript\">".$this->getAssetContents()."</script>";
    }
}
