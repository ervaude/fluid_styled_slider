<?php
namespace DanielGoerz\FluidStyledSlider\DataProcessing;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;

/**
 * This data processor will calculate the width of a slider
 * based on the included images and is used for the CType "fs_slider"
 */
class FluidStyledSliderProcessor implements DataProcessorInterface
{

    /**
     * Process data for the CType "fs_slider"
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     * @throws ContentRenderingException
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $options = array();
        // Calculating the total width of the slider
        $sliderWidth = 0;
        if ((int)$processedData['data']['imagewidth'] > 0) {
            $sliderWidth = (int)$processedData['data']['imagewidth'];
        } else {
            $files = $processedData['files'];
            /** @var \TYPO3\CMS\Core\Resource\FileReference $file */
            foreach ($files as $file) {
                $fileWidth = $this->getCroppedWidth($file);
                $sliderWidth = $fileWidth > $sliderWidth ? $fileWidth : $sliderWidth;
            }
        }

        // This will be available in fluid with {slider.options}
        $processedData['slider']['options'] = json_encode($this->getOptionsFromFlexFormData($processedData['data']));
        // This will be available in fluid with {slider.width}
        $processedData['slider']['width'] = $sliderWidth + 80;
        return $processedData;
    }

    /**
     * When retrieving the width for a media file
     * a possible cropping needs to be taken into account.
     *
     * @param FileInterface $fileObject
     * @return int
     */
    protected function getCroppedWidth(FileInterface $fileObject)
    {
        if (!$fileObject->hasProperty('crop') || empty($fileObject->getProperty('crop'))) {
            return $fileObject->getProperty('width');
        }
        $croppingConfiguration = json_decode($fileObject->getProperty('crop'), true);
        return (int)$croppingConfiguration['width'];
    }

    /**
     * @param array $row
     * @return array
     */
    protected function getOptionsFromFlexFormData(array $row)
    {
        $options = [];
        $flexFormAsArray = GeneralUtility::xml2array($row['pi_flexform']);
        if (!empty($flexFormAsArray['data']['sDEF']['lDEF']) && is_array($flexFormAsArray['data']['sDEF']['lDEF'])) {
            foreach ($flexFormAsArray['data']['sDEF']['lDEF'] as $optionKey => $optionValue) {
                $optionParts = explode('.', $optionKey);
                $options[array_pop($optionParts)] = $optionValue['vDEF'] === '1' ? true : $optionValue['vDEF'];
            }
        }
        return $options;
    }
}
