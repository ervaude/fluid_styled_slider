<?php
defined('TYPO3_MODE') or die();

// Include new content elements to modWizards
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:fluid_styled_slider/Configuration/PageTSconfig/FluidStyledSlider.ts">'
);

// Register for hook to show preview of tt_content element of CType="fluid_styled_slider" in page module
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['fluid_styled_slider']
    = \DanielGoerz\FluidStyledSlider\Hooks\FsSliderPreviewRenderer::class;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Fluid Styled Slider');

// Add a flexform to the fs_slider CType
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '',
    'FILE:EXT:fluid_styled_slider/Configuration/FlexForms/fs_slider_flexform.xml',
    'fs_slider'
);
