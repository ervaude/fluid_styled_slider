# About this extension

fluid_styled_slider is an example extension that provides everything to create a custom content element
for TYPO3 CMS 7 based on the system extension fluid_styled_content (FSC).

## System Requirements
Obviously FSC needs to be installed in TYPO3 which is only possible in version 7.5 or higher.

## Installation
Install the extension and include the static TypoScript. Simple as that.

## Components of a content element based on FSC
This extension adds a content element called `fs_slider` to the system. The following steps are necessary to get it up and running:

### PageTSconfig
To make our content element appear in the wizard for new content elements, we have to add it via PageTSconfig

    mod.wizards.newContentElement.wizardItems.common {
    	elements {
    		fs_slider {
    			iconIdentifier = content-image
    			title = LLL:EXT:fluid_styled_slider/Resources/Private/Language/locallang_be.xlf:wizard.title
    			description = LLL:EXT:fluid_styled_slider/Resources/Private/Language/locallang_be.xlf:wizard.description
    			tt_content_defValues {
    				CType = fs_slider
    			}
    		}
    	}
    	show := addToList(fs_slider)
    }

### TCA
Now we need to tell TYPO3 what fields to show in the backend. Therefore we have to extend the tt_content TCA configuration.
This stuff is now done in the folder `Configuration/TCA/Override`. Let's add our new CType first (this could also be done in `ext_tables.php`):

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            'LLL:EXT:fluid_styled_slider/Resources/Private/Language/locallang_be.xlf:wizard.title',
            'fs_slider',
            'content-image'
        ],
        'textmedia',
        'after'
    );
    
Now we determine what fields to show for our CType:

    $GLOBALS['TCA']['tt_content']['types']['fs_slider'] = [
        'showitem'         => '
                --palette--;' . $frontendLanguageFilePrefix . 'palette.general;general,
                --palette--;' . $languageFilePrefix . 'tt_content.palette.mediaAdjustments;mediaAdjustments,
                pi_flexform,
            --div--;' . $customLanguageFilePrefix . 'tca.tab.sliderElements,
                 assets
        '
    ];

### TypoScript
The new CType `fs_slider` needs a rendering definition. This is rather simple:

    tt_content {
    	fs_slider < lib.fluidContent
    	fs_slider {
    		templateName = FluidStyledSlider
    		dataProcessing {
    			10 = TYPO3\CMS\Frontend\DataProcessing\FilesProcessor
    			10 {
    				references.fieldName = assets
    			}
    			20 = DanielGoerz\FluidStyledSlider\DataProcessing\FluidStyledSliderProcessor
    		}
    	}
    }

The `lib.fluidContent` is not much more than the initialization of a `FLUIDCONTENT` object. We just change the template name
(make sure to at least add your template path to `lib.fluidContent.templateRootPaths`)
and specify which dataProcessors we are gonna use. Oh right, dataProcessors.

### DataProcessors
Those are PHP classes that get the data before it is passed to the fluidtemplate. They can manipulate the data or add stuff to
be present in the template. The `TYPO3\CMS\Frontend\DataProcessing\FilesProcessor`
for instance resolves all attached media elements for us, so we can access the FileReference objects in the view.
`DanielGoerz\FluidStyledSlider\DataProcessing\FluidStyledSliderProcessor` is a custom processor to illustrate how this works.

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
        public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
        {
            // Content of $processedData will be available in the template
            // It can be processed here to your needs.
            $processedData['slider']['width'] = 1000;
            return $processedData;
        }

However, DataProcessors are optional.

### The Fluid template
The last piece in the puzzle is the actual template that receives the data processed by all specified DataProcessors in the end.
This is plain fluid as we know (and love) it:

    <html xmlns:f="http://xsd.helhum.io/ns/typo3/cms-fluid/master/ViewHelpers">
    <div class="fluid-styled-slider" style="width: {slider.width}px; margin: auto">
    	<f:for each="{files}" as="file">
    		<figure class="fluid-styled-slider-item">
    			<f:image image="{file}" height="{data.imageheight}" width="{data.imagewidth}" alt="{file.properties.alt}" title="{file.properties.title}"/>
    			<f:if condition="{file.properties.description}">
    				<figcaption>{file.properties.description}</figcaption>
    			</f:if>
    		</figure>
    	</f:for>
    </div>
    </html>

Of course we can use Layouts and Partials here as well. Note how `{data}` contains the tt_content row from the rendered
content element. `{files}` is added by the `TYPO3\CMS\Frontend\DataProcessing\FilesProcessor` and contains the attached media
as proper objects. `{slider.width}` is added by our own DataProcessor.

### Optional: Preview in Page Module
We probably want some kind of preview for our editors in the page module. There are two notable possibilities to achieve this:

#### Fluid template via PageTSconfig
We can simply specify a fluid template to be rendered as preview in PageTSconfig:

    web_layout.tt_content.preview.fs_slider = EXT:fluid_styled_slider/Resources/Private/Templates/Preview/FluidStyledSlider.html

This template will receive all fields of the tt_content row directly. So `{header}` contains the header, `{bodytext}` contains the
bodytext and so on.

#### tt_content_drawItem Hook
If we want to do more sophisticated stuff like getting child records resolved, we can register to the `tt_content_drawItem` hook
like this:

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['fluid_styled_slider']
        = \DanielGoerz\FluidStyledSlider\Hooks\FsSliderPreviewRenderer::class;

Our class has to implement the `\TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface`.

    class FsSliderPreviewRenderer implements PageLayoutViewDrawItemHookInterface
    {
        /**
         * Preprocesses the preview rendering of a content element of type "fs_slider"
         *
         * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
         * @param bool $drawItem Whether to draw the item using the default functionality
         * @param string $headerContent Header content
         * @param string $itemContent Item content
         * @param array $row Record row of tt_content
         * @return void
         */
        public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
        {
            if ($row['CType'] === 'fs_slider') {
                $itemContent .= '<h3>Fluid Styled Slider</h3>';
                if ($row['assets']) {
                    $itemContent .= $parentObject->thumbCode($row, 'tt_content', 'assets') . '<br />';
                }
                $drawItem = false;
            }
        }
    }

Whatever we write into `$itemContent` will be rendered in the page module inside our content element. 

## Miscellaneous
This extension includes jQuery in `JSFooterLibs`. If you already have jQuery on your site, overwrite this in your TypoScript
or set the constant `plugin.tx_fluidstyledslider.settings.includejQuery` to 0.