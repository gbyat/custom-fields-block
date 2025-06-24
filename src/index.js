import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
    useInnerBlocksProps,
    RichText,
    BlockControls,
    AlignmentToolbar
} from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    ToggleControl,
    RangeControl,
    ColorPalette,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

registerBlockType('custom-fields-block/custom-field', {
    edit: function Edit({ attributes, setAttributes, clientId }) {
        const {
            fieldKey,
            displayType,
            typography,
            colors,
            spacing,
            alignment
        } = attributes;

        const [customFields, setCustomFields] = useState([]);
        const [fieldValue, setFieldValue] = useState('');

        // Fetch custom fields on component mount
        useEffect(() => {
            if (window.cfbData && window.cfbData.customFields) {
                setCustomFields(window.cfbData.customFields);

                // Set field value if fieldKey is selected
                if (fieldKey) {
                    const selectedField = window.cfbData.customFields.find(field => field.key === fieldKey);
                    if (selectedField) {
                        setFieldValue(selectedField.value);
                    }
                }
            }
        }, [fieldKey]);

        const blockProps = useBlockProps({
            className: `cfb-block ${alignment ? `has-text-align-${alignment}` : ''}`
        });

        const updateFieldValue = (newFieldKey) => {
            setAttributes({ fieldKey: newFieldKey });
            if (newFieldKey) {
                const selectedField = customFields.find(field => field.key === newFieldKey);
                if (selectedField) {
                    setFieldValue(selectedField.value);
                }
            } else {
                setFieldValue('');
            }
        };

        const updateTypography = (property, value) => {
            setAttributes({
                typography: {
                    ...typography,
                    [property]: value
                }
            });
        };

        const updateColors = (property, value) => {
            setAttributes({
                colors: {
                    ...colors,
                    [property]: value
                }
            });
        };

        const updateSpacing = (property, value) => {
            setAttributes({
                spacing: {
                    ...spacing,
                    [property]: value
                }
            });
        };

        // Build inline styles for preview
        const previewStyles = {};
        if (typography.fontSize) previewStyles.fontSize = `${typography.fontSize}px`;
        if (typography.fontWeight) previewStyles.fontWeight = typography.fontWeight;
        if (typography.lineHeight) previewStyles.lineHeight = typography.lineHeight;
        if (typography.letterSpacing) previewStyles.letterSpacing = `${typography.letterSpacing}px`;
        if (colors.textColor) previewStyles.color = colors.textColor;
        if (colors.backgroundColor) previewStyles.backgroundColor = colors.backgroundColor;
        if (spacing.marginTop) previewStyles.marginTop = `${spacing.marginTop}px`;
        if (spacing.marginBottom) previewStyles.marginBottom = `${spacing.marginBottom}px`;
        if (spacing.paddingTop) previewStyles.paddingTop = `${spacing.paddingTop}px`;
        if (spacing.paddingBottom) previewStyles.paddingBottom = `${spacing.paddingBottom}px`;

        const renderPreview = () => {
            if (!fieldValue) {
                return (
                    <div style={{
                        padding: '20px',
                        border: '2px dashed #ccc',
                        textAlign: 'center',
                        color: '#666',
                        backgroundColor: '#f9f9f9'
                    }}>
                        <div style={{ marginBottom: '15px' }}>
                            <strong>{__('Custom Field Block', 'custom-fields-block')}</strong>
                        </div>
                        <SelectControl
                            label={__('Custom Field auswählen:', 'custom-fields-block')}
                            value={fieldKey}
                            options={[
                                { label: __('-- Feld auswählen --', 'custom-fields-block'), value: '' },
                                ...customFields.map(field => ({
                                    label: field.label,
                                    value: field.key
                                }))
                            ]}
                            onChange={updateFieldValue}
                        />
                        <div style={{
                            marginTop: '10px',
                            fontSize: '12px',
                            color: '#888',
                            fontStyle: 'italic'
                        }}>
                            {__('Wählen Sie ein Custom Field aus der Liste oben aus', 'custom-fields-block')}
                        </div>
                    </div>
                );
            }

            const content = displayType === 'heading' ?
                <h2 style={previewStyles}>{fieldValue}</h2> :
                <p style={previewStyles}>{fieldValue}</p>;

            return (
                <div style={{ position: 'relative' }}>
                    {content}
                    {/* Quick field selector overlay */}
                    <div style={{
                        position: 'absolute',
                        top: '-10px',
                        right: '-10px',
                        background: '#fff',
                        border: '1px solid #ddd',
                        borderRadius: '4px',
                        padding: '5px',
                        boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
                        zIndex: 10,
                        minWidth: '200px'
                    }}>
                        <SelectControl
                            label={__('Feld ändern:', 'custom-fields-block')}
                            value={fieldKey}
                            options={[
                                { label: __('-- Feld auswählen --', 'custom-fields-block'), value: '' },
                                ...customFields.map(field => ({
                                    label: field.label,
                                    value: field.key
                                }))
                            ]}
                            onChange={updateFieldValue}
                        />
                    </div>
                </div>
            );
        };

        return (
            <>
                <BlockControls>
                    <AlignmentToolbar
                        value={alignment}
                        onChange={(newAlignment) => setAttributes({ alignment: newAlignment })}
                    />
                </BlockControls>

                <InspectorControls>
                    <PanelBody title={__('Custom Field Einstellungen', 'custom-fields-block')} initialOpen={true}>
                        <SelectControl
                            label={__('Custom Field auswählen', 'custom-fields-block')}
                            value={fieldKey}
                            options={[
                                { label: __('-- Feld auswählen --', 'custom-fields-block'), value: '' },
                                ...customFields.map(field => ({
                                    label: field.label,
                                    value: field.key
                                }))
                            ]}
                            onChange={updateFieldValue}
                        />

                        <SelectControl
                            label={__('Anzeigetyp', 'custom-fields-block')}
                            value={displayType}
                            options={[
                                { label: __('Absatz', 'custom-fields-block'), value: 'paragraph' },
                                { label: __('Überschrift', 'custom-fields-block'), value: 'heading' }
                            ]}
                            onChange={(value) => setAttributes({ displayType: value })}
                        />
                    </PanelBody>

                    <PanelBody title={__('Typografie', 'custom-fields-block')} initialOpen={false}>
                        <RangeControl
                            label={__('Schriftgröße (px)', 'custom-fields-block')}
                            value={typography.fontSize}
                            onChange={(value) => updateTypography('fontSize', value)}
                            min={12}
                            max={72}
                            step={1}
                        />

                        <SelectControl
                            label={__('Schriftgewicht', 'custom-fields-block')}
                            value={typography.fontWeight}
                            options={[
                                { label: __('Normal', 'custom-fields-block'), value: 'normal' },
                                { label: __('Fett', 'custom-fields-block'), value: 'bold' },
                                { label: __('100', 'custom-fields-block'), value: '100' },
                                { label: __('200', 'custom-fields-block'), value: '200' },
                                { label: __('300', 'custom-fields-block'), value: '300' },
                                { label: __('400', 'custom-fields-block'), value: '400' },
                                { label: __('500', 'custom-fields-block'), value: '500' },
                                { label: __('600', 'custom-fields-block'), value: '600' },
                                { label: __('700', 'custom-fields-block'), value: '700' },
                                { label: __('800', 'custom-fields-block'), value: '800' },
                                { label: __('900', 'custom-fields-block'), value: '900' }
                            ]}
                            onChange={(value) => updateTypography('fontWeight', value)}
                        />

                        <RangeControl
                            label={__('Zeilenhöhe', 'custom-fields-block')}
                            value={typography.lineHeight}
                            onChange={(value) => updateTypography('lineHeight', value)}
                            min={1}
                            max={3}
                            step={0.1}
                        />

                        <RangeControl
                            label={__('Buchstabenabstand (px)', 'custom-fields-block')}
                            value={typography.letterSpacing}
                            onChange={(value) => updateTypography('letterSpacing', value)}
                            min={-2}
                            max={10}
                            step={0.1}
                        />
                    </PanelBody>

                    <PanelBody title={__('Farben', 'custom-fields-block')} initialOpen={false}>
                        <div>
                            <label>{__('Textfarbe', 'custom-fields-block')}</label>
                            <ColorPalette
                                value={colors.textColor}
                                onChange={(value) => updateColors('textColor', value)}
                            />
                        </div>

                        <div style={{ marginTop: '20px' }}>
                            <label>{__('Hintergrundfarbe', 'custom-fields-block')}</label>
                            <ColorPalette
                                value={colors.backgroundColor}
                                onChange={(value) => updateColors('backgroundColor', value)}
                            />
                        </div>
                    </PanelBody>

                    <PanelBody title={__('Abstände', 'custom-fields-block')} initialOpen={false}>
                        <RangeControl
                            label={__('Abstand oben (px)', 'custom-fields-block')}
                            value={spacing.marginTop}
                            onChange={(value) => updateSpacing('marginTop', value)}
                            min={0}
                            max={100}
                            step={1}
                        />

                        <RangeControl
                            label={__('Abstand unten (px)', 'custom-fields-block')}
                            value={spacing.marginBottom}
                            onChange={(value) => updateSpacing('marginBottom', value)}
                            min={0}
                            max={100}
                            step={1}
                        />

                        <RangeControl
                            label={__('Innenabstand oben (px)', 'custom-fields-block')}
                            value={spacing.paddingTop}
                            onChange={(value) => updateSpacing('paddingTop', value)}
                            min={0}
                            max={100}
                            step={1}
                        />

                        <RangeControl
                            label={__('Innenabstand unten (px)', 'custom-fields-block')}
                            value={spacing.paddingBottom}
                            onChange={(value) => updateSpacing('paddingBottom', value)}
                            min={0}
                            max={100}
                            step={1}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    {renderPreview()}
                </div>
            </>
        );
    },

    save: function Save() {
        // This block uses a PHP render callback, so we don't need to save anything
        return null;
    }
}); 