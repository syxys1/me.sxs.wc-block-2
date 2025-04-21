/**
 * WooCommerce Category Accordion Block - Editor Script
 * Version: 1.0.0
 * 
 * Handles the block editor interface and preview rendering
 */
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { PanelBody, SelectControl, ToggleControl, RangeControl, ColorPicker, TextControl, Button } = wp.components;
const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck } = wp.blockEditor;

/**
 * Register the block type with WordPress
 */
wp.domReady(() => {
    // Vérifie et ajoute la catégorie si elle n'est pas encore définie.
    const currentCategories = wp.blocks.getCategories();
    const hasSXSCategory = currentCategories.some(
        (category) => category.slug === 'me-sxs-category'
    );

    if (!hasSXSCategory) {
        wp.blocks.setCategories([
            ...currentCategories,
            {
                slug: 'me-sxs-category',
                title: __('SXS Custom Blocks', 'sxs-wc-block'),
                icon: 'category',
            },
        ]);
    }

    // Enregistrer le bloc
//     const imageControls = wp.element.createElement(
//         MediaUploadCheck,
//         null,
//         wp.element.createElement(MediaUpload, {
//             onSelect: (media) => setAttributes({ caretImage: media.url }),
//             allowedTypes: ['image'],
//             value: attributes.caretImage,
//             render: ({ open }) => wp.element.createElement(
//                 'div',
//                 { className: 'caret-image-control' },
//                 [
//                     attributes.caretImage && wp.element.createElement(
//                         'img',
//                         {
//                             src: attributes.caretImage,
//                             className: 'caret-preview',
//                             alt: ''
//                         }
//                     ),
//                     wp.element.createElement(
//                         Button,
//                         {
//                             onClick: open,
//                             variant: 'secondary'
//                         },
//                         attributes.caretImage ? 
//                             __('Changer l\'icône', 'sxs-wc-blocks') : 
//                             __('Choisir une icône', 'sxs-wc-blocks')
//                     ),
//                     attributes.caretImage && wp.element.createElement(
//                         Button,
//                         {
//                             onClick: () => setAttributes({ caretImage: '' }),
//                             variant: 'link',
//                             isDestructive: true
//                         },
//                         __('Supprimer', 'sxs-wc-blocks')
//                     )
//                 ].filter(Boolean)
//             )
//         })
//     );

//     return wp.element.createElement(
//         'div',
//         { 
//             className: `sxs-accordion-block${attributes.isOpen ? ' is-open' : ''}`,
//             onClick: () => setAttributes({ isOpen: !attributes.isOpen })
//         },
//         [
//             wp.element.createElement(InspectorControls, {},
//                 wp.element.createElement(PanelBody, 
//                     { title: __('Paramètres de l\'accordéon', 'sxs-wc-blocks') },
//                     imageControls
//                 )
//             ),
//             wp.element.createElement(
//                 'div',
//                 { className: 'sxs-accordion-header' },
//                 [
//                     wp.element.createElement('span', 
//                         { 
//                             className: 'sxs-accordion-icon',
//                             style: attributes.caretImage ? {
//                                 backgroundImage: `url(${attributes.caretImage})`,
//                                 transform: attributes.isOpen ? 'rotate(90deg)' : 'none'
//                             } : {}
//                         }
//                     ),
//                     wp.element.createElement(InnerBlocks)
//                 ]
//             )
//         ]
//     );
// },


    registerBlockType('me-sxs-block/category-accordion', {
        title: __('Category Accordion', 'sxs-wc-block'),
        icon: 'menu',
        category: 'me-sxs-category',
        description: __('Display WooCommerce products in a category accordion layout.', 'sxs-wc-block'),

        /**
         * Block attributes configuration
         */
        attributes: {
            // Theme integration defaults
            useThemeStyles: {
                type: 'boolean',
                default: true
            },
            excludeCategories: { 
                type: 'array', 
                default: [] 
            },
            orderBy: { 
                type: 'string', 
                default: 'date' 
            },
            order: { 
                type: 'string', 
                default: 'DESC' 
            },
            columns: { 
                type: 'number', 
                default: 4 
            },
            title: { 
                type: 'string', 
                default: 'Product Collection' 
            },
            showSubcategories: { 
                type: 'boolean', 
                default: true 
            },
            titleFontSize: { 
                type: 'number', 
                default: 24 
            },
            titleFontColor: { 
                type: 'string', 
                default: '#333333' 
            },
            separatorColor: { 
                type: 'string', 
                default: '#dddddd' 
            },
            separatorThickness: { 
                type: 'number', 
                default: 1 
            },
            showPrice: { 
                type: 'boolean', 
                default: true 
            },
            showAddToCart: { 
                type: 'boolean', 
                default: true 
            },
            productFontSize: { 
                type: 'number', 
                default: 14 
            },
            productMargin: { 
                type: 'number', 
                default: 10 
            },
            productBorderColor: { 
                type: 'string', 
                default: '#dddddd' 
            },
            productBorderStyle: { 
                type: 'string', 
                default: 'solid' 
            },
            accordionTitleFontSize: { 
                type: 'number', 
                default: 18 
            },
            accordionTitleFontColor: { 
                type: 'string', 
                default: '#333' 
            },
            accordionCaretColor: { 
                type: 'string', 
                default: '#000' 
            },
            accordionCaretImage: { 
                type: 'string', 
                default: '' 
            },
        },

        /**
         * Block editor component
         * @param {Object} props - Component props
         * @param {Object} props.attributes - Block attributes
         * @param {Function} props.setAttributes - Attribute setter
         */
        edit: ({ attributes, setAttributes }) => {
            const {
                useThemeStyles,
                excludeCategories, 
                orderBy, 
                order, 
                columns, 
                title, 
                showSubcategories,
                titleFontSize, 
                titleFontColor, 
                separatorColor, 
                separatorThickness,
                showPrice, 
                showAddToCart, 
                productFontSize, 
                productMargin,
                productPadding,
                productBorderColor, 
                productBorderStyle,
                productBorderRadius,
                accordionTitleFontSize, 
                accordionTitleFontColor, 
                accordionCaretColor, 
                accordionCaretImage,
            } = attributes;

            // // Configure block props for editor preview
            // const blockProps = useBlockProps({
            //     className: 'sxs-category-accordion-editor-preview',
            //     style: {
            //         border: '1px dashed #ccc',
            //         padding: '20px',
            //         marginBottom: '20px',
            //         backgroundColor: '#f8f8f8',
            //         maxWidth: '100%',
            //         overflow: 'hidden'
            //     }
            // });

            // Get categories data from localized script
            const allCategories = window.sxsCategoryAccordionData?.categories || [];

            /**
             * Handle category exclusion
             * @param {string} slug - Category slug to toggle
             */
            const toggleExcludedCategory = (slug) => {
                const newExcludes = excludeCategories.includes(slug)
                    ? excludeCategories.filter(cat => cat !== slug)
                    : [...excludeCategories, slug];
                setAttributes({ excludeCategories: newExcludes });
            };

            return createElement(
                'div', // ...blockProps>
                null,
                
                /* Inspector Controls - Block Settings */
                createElement( InspectorControls,
                    null,
                    /* General Settings Panel */
                    createElement( PanelBody, { 
                        title: __('General Settings', 'sxs-wc-block'),
                        initialOpen: true
                        },
                        createElement( ToggleControl, { 
                            label: __('Use Theme Styles', 'sxs-wc-block'),
                            help: __('Automatically apply theme colors and typography', 'sxs-wc-block'),
                            checked: useThemeStyles,
                            onChange: (checked) => setAttributes({ useThemeStyles: checked }),
                            }),
                        createElement( SelectControl, { 
                            label: __('Exclude Categories', 'sxs-wc-block'),
                            multiple: true,
                            value: excludeCategories,
                            options: allCategories.map(cat => ({ label: cat.name, value: cat.slug })),
                            onChange: (selected) => setAttributes({ excludeCategories: selected }),
                            }),
                        createElement( TextControl, { 
                            label: __('Block Title', 'sxs'),
                            placeholder: __('Enter a title...', 'sxs-wc-block'),
                            value: title,
                            onChange: (newTitle) => setAttributes({ title: newTitle }),
                            }),
                        createElement( ToggleControl, { 
                            label: __('Show Subcategory Titles', 'sxs-wc-block'),
                            checked: showSubcategories,
                            onChange: (checked) => setAttributes({ showSubcategories: checked }),
                            }),
                        createElement( RangeControl, { 
                            label: __('Columns', 'sxs-wc-block'),
                            value: columns,
                            onChange: (value) => setAttributes({ columns: value }),
                            min: 1,
                            max: 6,
                            }),
                        createElement( SelectControl, { 
                            label: __('Order By', 'sxs-wc-block'),
                            value: orderBy,
                            options: [
                                { label: 'Date', value: 'date' },
                                { label: 'Title', value: 'title' },
                                { label: 'Price', value: 'meta_value' },
                                { label: 'Category', value: 'category' },
                            ],
                            onChange: (value) => setAttributes({ orderBy: value }),
                            }),
                        createElement( SelectControl, { 
                            label: __('Order', 'sxs-wc-block'),
                            value: order,
                            options: [
                                { label: 'Ascending', value: 'ASC' },
                                { label: 'Descending', value: 'DESC' },
                            ],
                            onChange: (value) => setAttributes({ order: value }),
                            }),
                    ),
                    /* Accordion Settings Panel */
                    createElement( PanelBody, { 
                        title: __('Accordion Settings', 'sxs-wc-block') },
                        createElement( RangeControl, { 
                            label: __('Title Font Size', 'sxs-wc-block'),
                            value: accordionTitleFontSize,
                            onChange: (value) => setAttributes({ accordionTitleFontSize: value }),
                            min: 14,
                            max: 30,
                        }),
                        createElement( ColorPicker, { 
                            label: __('Title Font Color', 'sxs-wc-block'),
                            value: accordionTitleFontColor,
                            onChange: (value) => setAttributes({ accordionTitleFontColor: value }),
                        }),
                        createElement( ColorPicker, { 
                            label: __('Caret Color', 'sxs-wc-block'),
                            value: accordionCaretColor,
                            onChange: (value) => setAttributes({ accordionCaretColor: value }),
                        }),
                        createElement(MediaUploadCheck, null,
                            createElement(MediaUpload, {
                                onSelect: (media) => setAttributes({ accordionCaretImage: media.url }),
                                allowedTypes: ['image'],
                                value: accordionCaretImage,
                                render: ({ open }) => (
                                    createElement(Button, {
                                        onClick: open,
                                        isSecondary: true,
                                        style: { marginTop: '10px' }
                                    }, accordionCaretImage ? __('Change Caret Image', 'sxs-wc-block') : __('Select Caret Image', 'sxs-wc-block'))
                                ),
                            })
                        ),
                        accordionCaretImage && createElement('img', {
                            src: accordionCaretImage,
                            alt: __('Caret Image Preview', 'sxs-wc-block'),
                            style: { marginTop: '10px', maxWidth: '100%', height: 'auto' }
                        })
                    ),
                    /* Category Settings Panel */
                    createElement( PanelBody, { 
                        title: __('Category Settings', 'sxs-wc-block'),
                        initialOpen: false 
                        },
                        createElement( RangeControl, { 
                            label: __('Category Title Font Size', 'sxs-wc-block'),
                            value: titleFontSize,
                            onChange: (value) => setAttributes({ titleFontSize: value }),
                            min: 16,
                            max: 36,
                        }),
                        createElement( ColorPicker, { 
                            label: __('Category Title Font Color', 'sxs-wc-block'),
                            value: titleFontColor,
                            onChange: (value) => setAttributes({ titleFontColor: value }),
                        }),
                        createElement( RangeControl, { 
                            label: __('Separator Thickness', 'sxs-wc-block'),
                            value: separatorThickness,
                            onChange: (value) => setAttributes({ separatorThickness: value }),
                            min: 1,
                            max: 10,
                        }),
                        createElement( ColorPicker, { 
                            label: __('Separator Color', 'sxs-wc-block'),
                            value: separatorColor,
                            onChange: (value) => setAttributes({ separatorColor: value }),
                        }),
                    ),
                    /* Product Settings Panel */
                    createElement( PanelBody, { 
                        title: __('Product Settings', 'sxs-wc-block'),
                        initialOpen: false  
                        },
                        createElement( RangeControl, { 
                            label: __('Product Title Font Size', 'sxs-wc-block'),
                            value: productFontSize,
                            onChange: (value) => setAttributes({ productFontSize: value }),
                            min: 12,
                            max: 18,
                        }),
                        createElement( RangeControl, { 
                            label: __('Product Margin', 'sxs-wc-block'),
                            value: productMargin,
                            onChange: (value) => setAttributes({ productMargin: value }),
                            min: 0,
                            max: 50,
                        }),
                        createElement( RangeControl, { 
                            label: __('Product Padding', 'sxs-wc-block'),
                            value: productPadding,
                            onChange: (value) => setAttributes({ productPadding: value }),
                            min: 0,
                            max: 50,
                        }),
                        createElement( RangeControl, { 
                            label: __('Product Border Radius', 'sxs-wc-block'),
                            value: productBorderRadius,
                            onChange: (value) => setAttributes({ productBorderRadius: value }),
                            min: 0,
                            max: 50,
                        }),                         
                        createElement( SelectControl, { 
                            label: __('Product Border Style', 'sxs-wc-block'),
                            value: productBorderStyle,
                            help: __('Choose the border style for product items', 'sxs-wc-block'),
                            options: [
                                { label: 'Solid', value: 'solid' },
                                { label: 'Dashed', value: 'dashed' },
                                { label: 'Dotted', value: 'dotted' },
                            ],
                            onChange: (value) => setAttributes({ productBorderStyle: value }),
                        }),  
                        createElement( ToggleControl, { 
                            label: __('Show Price', 'sxs-wc-block'),
                            checked: showPrice,
                            onChange: (checked) => setAttributes({ showPrice: checked }),
                        }),
                        createElement( ToggleControl, { 
                            label: __('Show Add to Cart Button', 'sxs-wc-block'),
                            checked: showAddToCart,
                            onChange: (checked) => setAttributes({ showAddToCart: checked }),
                        }),
                    ),
                ),
                /* Editor Preview */
                createElement( 'div', { 
                    className: 'editor-preview' },
                    createElement( 'h2', {
                        style: {
                            fontSize: `${titleFontSize}px`,
                            color: titleFontColor,
                            margin: '0 0 10px 0',
                        },
                    },
                        title || __('Product Collection', 'sxs')
                    ),
                    createElement( 'div', {
                        style: {
                            borderBottom: `${separatorThickness}px solid ${separatorColor}`,
                            margin: '10px 0 20px',
                        },
                    }),
                    createElement( 'div', {
                        style: {
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            overflow: 'hidden',
                            marginBottom: '20px',
                        },
                    },
                        createElement( 'div', {
                            style: {
                                padding: '10px 15px',
                                backgroundColor: '#f0f0f0',
                                borderBottom: '1px solid #ddd',
                                fontSize: `${accordionTitleFontSize}px`,
                                color: accordionTitleFontColor,
                                display: 'flex',
                                alignItems: 'center',
                            },
                        },
                            createElement( 'span', {
                                style: {
                                    color: accordionCaretColor,
                                    marginRight: '10px',
                                },
                            },
                            accordionCaretImage
                                ? createElement( 'img', {
                                    src: accordionCaretImage,
                                    alt: '',
                                    style: { width: '20px', height: '20px' },
                                })
                                : '▶'
                            ),
                            createElement( 'span', 
                                null,
                                __('Sample Category', 'sxs-wc-block')
                            ),
                        ),
                        createElement( 'div', {
                            style: {
                                padding: '15px',
                                backgroundColor: '#fff',
                                display: 'grid',
                                gridTemplateColumns: `repeat(${Math.min(columns, 4)}, 1fr)`,
                                gap: '15px',
                            },
                        },
                        Array.from({ length: Math.min(columns, 4) }).map((_, i) =>
                            createElement( 'div', {
                                key: i,
                                style: {
                                    border: `1px ${productBorderStyle} ${productBorderColor}`,
                                    padding: '10px',
                                    margin: `${productMargin}px`,
                                    fontSize: `${productFontSize}px`,
                                    textAlign: 'center',
                                    backgroundColor: '#fafafa',
                                },
                            },
                                createElement( 'div', {
                                    style: {
                                        width: '100%',
                                        height: '80px',
                                        backgroundColor: '#eee',
                                        marginBottom: '10px',
                                    },
                                }),
                                createElement( 'p', {
                                    style: {
                                        margin: '0 0 5px 0',
                                        fontWeight: 'bold',
                                    },
                                },
                                    __('Product', 'sxs-wc-block') + ` ${i + 1}`
                                ),
                            showPrice &&
                                createElement( 'p', { 
                                    style: { 
                                        margin: '0 0 5px 0',
                                    }
                                },
                                    '$XX.XX'
                                ),
                            showAddToCart &&
                                createElement( 'button', {
                                    style: {
                                        backgroundColor: '#efefef',
                                        border: '1px solid #ddd',
                                        padding: '5px 10px',
                                        fontSize: '12px',
                                    },
                                },
                                    __('Add to Cart', 'sxs-wc-block')
                                )
                            )
                        )
                        ),
                        createElement( 'p', {
                            style: {
                                fontSize: '12px',
                                fontStyle: 'italic',
                                color: '#666',
                                marginTop: '10px',
                            },
                        },
                            __('Actual products will be displayed on the frontend.', 'sxs-wc-block')
                        )
                    )
                ),
            );
        },

        // Server-side rendering
        save: () => null
    });
});