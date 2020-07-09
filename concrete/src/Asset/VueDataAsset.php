<?php
namespace Concrete\Core\Asset;

use Concrete\Core\Html\Object\HeadLink;
use Config;

class VueDataAsset extends Asset
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
     * @return string|null
     */
    public function getAssetContents()
    {
      return "ccm_vue_data = Object.assign(window.ccm_vue_data || {}, ".json_encode($this->getAssetURL()).")";
    }

    /**
     * @return string
     */
    public function getAssetType()
    {
        return 'vue-data';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "<script data-type=\"".$this->getAssetType()."\" type=\"text/javascript\">".$this->getAssetContents()."</script>";
    }
}
