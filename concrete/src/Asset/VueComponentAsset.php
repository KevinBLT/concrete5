<?php
namespace Concrete\Core\Asset;

use Concrete\Core\Html\Object\HeadLink;
use Config;

class VueComponentAsset extends Asset
{
    /**
     * @var bool
     */
    protected $assetSupportsMinification = true;

    /**
     * @var bool
     */
    protected $assetSupportsCombination  = true;

    /**
     * @return string
     */
    public function getAssetDefaultPosition()
    {
        return Asset::ASSET_POSITION_FOOTER;
    }

    /**
     * @return string
     */
    protected static function getDirectoryName()
    {
        return str_replace('css', 'vue-component', DIRNAME_CSS);
    }

    /**
     * @return string
     */
    protected static function getRelativeOutputDirectory()
    {
        return REL_DIR_FILES_CACHE.'/'.self::getDirectoryName();
    }

    /**
     * @return bool|string
     */
    protected static function getOutputDirectory()
    {
        if (!file_exists(Config::get('concrete.cache.directory').'/'.self::getDirectoryName())) {
            $proceed = @mkdir(Config::get('concrete.cache.directory').'/'.self::getDirectoryName());
        } else {
            $proceed = true;
        }
        if ($proceed) {
            return Config::get('concrete.cache.directory').'/'.self::getDirectoryName();
        } else {
            return false;
        }
    }

    /**
     * @param Asset[] $assets
     *
     * @return Asset[]
     */
    public static function process($assets)
    {

        if ($directory = self::getOutputDirectory()) {
            $relativeDirectory = self::getRelativeOutputDirectory();
            $filename          = '';
            $sourceFiles       = [];

            foreach ($assets as $asset) {
                $filename      .= $asset->getAssetHashKey();
                $sourceFiles[]  = $asset->getAssetHandle();
            }

            $filename  = sha1($filename);
            $cacheFile = $directory.'/'.$filename.'.vue-component';

            if (!file_exists($cacheFile)) {
                $templates = '';

                foreach ($assets as $asset) {
                    $contents = (string) $asset;

                    if (isset($contents)) {
                        $templates .= $contents."\n\n";
                    }
                }

                @file_put_contents($cacheFile, $templates);
            }

            $asset = new self();
            $asset->setAssetURL($relativeDirectory.'/'.$filename.'.vue-component');
            $asset->setAssetPath($directory.'/'.$filename.'.vue-component');
            $asset->setCombinedAssetSourceFiles($sourceFiles);

            return array($asset);
        }

        return $assets;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Asset\AssetInterface::getAssetContents()
     */
    public function getAssetContents()
    {
        $result = @file_get_contents($this->getAssetPath());

        if ($resutl !== false) {
          $result = preg_replace('/\s\s+/', '', $result);
          $result = str_replace('<template>',  '<script type="text/x-template" id="'.$this->getAssetHandle().'">', $result);
          $result = str_replace('</template>', '</script>', $result);

          return $result;
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getAssetType()
    {
        return 'vue-component';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAssetContents();
    }
}
