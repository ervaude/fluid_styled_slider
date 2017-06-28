<?php
namespace DanielGoerz\FluidStyledSlider\DataProcessing;

/*
 * This file is part of the TYPO3 CMS extension fluid_styled_content.
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

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\FlexFormService;
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

        $cropString = $fileObject->getProperty('crop');
        // TYPO3 7LTS
        $croppingConfiguration = json_decode($cropString, true);
        if (!empty($croppingConfiguration['width'])) {
            return (int)$croppingConfiguration['width'];
        }

        // TYPO3 8LTS
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $width = 0;
        foreach (array_keys($croppingConfiguration) as $cropVariant) {
            $cropArea = $cropVariantCollection->getCropArea($cropVariant);
            if ($cropArea->isEmpty()) {
                continue;
            }
            $cropResult = json_decode((string)$cropArea->makeAbsoluteBasedOnFile($fileObject), true);
            if (!empty($cropResult['width']) && (int)$cropResult['width'] > $width) {
                $width = (int)$cropResult['width'];
            }
        }
        return $width;
    }

    /**
     * @param array $row
     * @return array
     */
    protected function getOptionsFromFlexFormData(array $row)
    {
        $options = [];
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $flexFormAsArray = $flexFormService->convertFlexFormContentToArray($row['pi_flexform']);
        foreach ($flexFormAsArray['options'] as $optionKey => $optionValue) {
            switch ($optionValue) {
                case '1':
                    $options[$optionKey] = true;
                    break;
                case '0':
                    $options[$optionKey] = false;
                    break;
                default:
                    $options[$optionKey] = $optionValue;
            }
        }
        return $options;
    }
}
