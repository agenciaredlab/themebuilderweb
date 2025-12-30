/**
 * Theme Builder Pro - Visual Editor
 * React-based drag & drop page builder
 */

(function($, React, ReactDOM) {
    'use strict';

    const { useState, useEffect, useRef, useCallback, useMemo, createContext, useContext, useReducer } = React;
    const { createRoot } = ReactDOM;

    // ============================================
    // CONTEXT & STATE MANAGEMENT
    // ============================================

    const EditorContext = createContext(null);
    const HistoryContext = createContext(null);

    // Editor State Reducer
    const editorReducer = (state, action) => {
        switch (action.type) {
            case 'SET_ELEMENTS':
                return { ...state, elements: action.payload };
            case 'ADD_ELEMENT':
                return { ...state, elements: [...state.elements, action.payload] };
            case 'UPDATE_ELEMENT':
                return {
                    ...state,
                    elements: state.elements.map(el =>
                        el.id === action.payload.id ? { ...el, ...action.payload.data } : el
                    )
                };
            case 'DELETE_ELEMENT':
                return {
                    ...state,
                    elements: state.elements.filter(el => el.id !== action.payload),
                    selectedElement: state.selectedElement === action.payload ? null : state.selectedElement
                };
            case 'DUPLICATE_ELEMENT':
                const elementToDuplicate = state.elements.find(el => el.id === action.payload);
                if (!elementToDuplicate) return state;
                const duplicated = {
                    ...JSON.parse(JSON.stringify(elementToDuplicate)),
                    id: generateId()
                };
                const index = state.elements.findIndex(el => el.id === action.payload);
                const newElements = [...state.elements];
                newElements.splice(index + 1, 0, duplicated);
                return { ...state, elements: newElements };
            case 'MOVE_ELEMENT':
                const { fromIndex, toIndex } = action.payload;
                const movedElements = [...state.elements];
                const [removed] = movedElements.splice(fromIndex, 1);
                movedElements.splice(toIndex, 0, removed);
                return { ...state, elements: movedElements };
            case 'SELECT_ELEMENT':
                return { ...state, selectedElement: action.payload };
            case 'SET_DEVICE':
                return { ...state, currentDevice: action.payload };
            case 'SET_PANEL':
                return { ...state, activePanel: action.payload };
            case 'SET_SAVING':
                return { ...state, isSaving: action.payload };
            case 'SET_PREVIEW':
                return { ...state, isPreview: action.payload };
            case 'SET_DIRTY':
                return { ...state, isDirty: action.payload };
            default:
                return state;
        }
    };

    // History Reducer for Undo/Redo
    const historyReducer = (state, action) => {
        switch (action.type) {
            case 'PUSH':
                const newPast = [...state.past.slice(-49), state.present];
                return {
                    past: newPast,
                    present: action.payload,
                    future: []
                };
            case 'UNDO':
                if (state.past.length === 0) return state;
                const previous = state.past[state.past.length - 1];
                const newPastUndo = state.past.slice(0, -1);
                return {
                    past: newPastUndo,
                    present: previous,
                    future: [state.present, ...state.future]
                };
            case 'REDO':
                if (state.future.length === 0) return state;
                const next = state.future[0];
                const newFuture = state.future.slice(1);
                return {
                    past: [...state.past, state.present],
                    present: next,
                    future: newFuture
                };
            case 'RESET':
                return {
                    past: [],
                    present: action.payload,
                    future: []
                };
            default:
                return state;
        }
    };

    // ============================================
    // UTILITIES
    // ============================================

    function generateId() {
        return 'tbp_' + Math.random().toString(36).substr(2, 9);
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function deepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    // ============================================
    // API SERVICE
    // ============================================

    const API = {
        baseUrl: tbpEditor.restUrl,
        nonce: tbpEditor.nonce,

        async request(endpoint, method = 'GET', data = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                }
            };

            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(`${this.baseUrl}${endpoint}`, options);

            if (!response.ok) {
                throw new Error(`API Error: ${response.statusText}`);
            }

            return response.json();
        },

        getDocument(id) {
            return this.request(`/documents/${id}`);
        },

        saveDocument(id, data) {
            return this.request(`/documents/${id}`, 'POST', data);
        },

        getWidgets() {
            return this.request('/widgets');
        },

        getTemplates(type = '') {
            return this.request(`/templates${type ? `?type=${type}` : ''}`);
        },

        getGlobalStyles() {
            return this.request('/global-styles');
        },

        saveGlobalStyles(styles) {
            return this.request('/global-styles', 'POST', styles);
        },

        getDynamicTags() {
            return this.request('/dynamic-tags');
        },

        getHistory(id) {
            return this.request(`/history/${id}`);
        }
    };

    // ============================================
    // COMPONENTS - CONTROLS
    // ============================================

    // Text Control
    const TextControl = ({ label, value, onChange, placeholder = '' }) => {
        return React.createElement('div', { className: 'tbp-control tbp-control-text' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('input', {
                type: 'text',
                className: 'tbp-control-input',
                value: value || '',
                onChange: (e) => onChange(e.target.value),
                placeholder
            })
        );
    };

    // Textarea Control
    const TextareaControl = ({ label, value, onChange, rows = 4 }) => {
        return React.createElement('div', { className: 'tbp-control tbp-control-textarea' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('textarea', {
                className: 'tbp-control-textarea-input',
                value: value || '',
                onChange: (e) => onChange(e.target.value),
                rows
            })
        );
    };

    // Number Control
    const NumberControl = ({ label, value, onChange, min, max, step = 1 }) => {
        return React.createElement('div', { className: 'tbp-control tbp-control-number' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('input', {
                type: 'number',
                className: 'tbp-control-input',
                value: value || 0,
                onChange: (e) => onChange(parseFloat(e.target.value)),
                min,
                max,
                step
            })
        );
    };

    // Slider Control
    const SliderControl = ({ label, value, onChange, min = 0, max = 100, step = 1, unit = 'px' }) => {
        const [localValue, setLocalValue] = useState(value || min);

        useEffect(() => {
            setLocalValue(value || min);
        }, [value, min]);

        const handleChange = (newValue) => {
            setLocalValue(newValue);
            onChange(newValue);
        };

        return React.createElement('div', { className: 'tbp-control tbp-control-slider' },
            React.createElement('div', { className: 'tbp-control-header' },
                React.createElement('label', { className: 'tbp-control-label' }, label),
                React.createElement('div', { className: 'tbp-control-value' },
                    React.createElement('input', {
                        type: 'number',
                        value: localValue,
                        onChange: (e) => handleChange(parseFloat(e.target.value)),
                        min,
                        max,
                        step
                    }),
                    React.createElement('span', { className: 'tbp-control-unit' }, unit)
                )
            ),
            React.createElement('input', {
                type: 'range',
                className: 'tbp-control-slider-input',
                value: localValue,
                onChange: (e) => handleChange(parseFloat(e.target.value)),
                min,
                max,
                step
            })
        );
    };

    // Color Control
    const ColorControl = ({ label, value, onChange }) => {
        const [isOpen, setIsOpen] = useState(false);
        const [localColor, setLocalColor] = useState(value || '#000000');
        const popoverRef = useRef(null);

        useEffect(() => {
            setLocalColor(value || '#000000');
        }, [value]);

        useEffect(() => {
            const handleClickOutside = (e) => {
                if (popoverRef.current && !popoverRef.current.contains(e.target)) {
                    setIsOpen(false);
                }
            };
            document.addEventListener('mousedown', handleClickOutside);
            return () => document.removeEventListener('mousedown', handleClickOutside);
        }, []);

        const presetColors = [
            '#000000', '#ffffff', '#ff0000', '#00ff00', '#0000ff',
            '#ffff00', '#ff00ff', '#00ffff', '#ff6600', '#6600ff',
            '#333333', '#666666', '#999999', '#cccccc', '#f5f5f5'
        ];

        return React.createElement('div', { className: 'tbp-control tbp-control-color', ref: popoverRef },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('div', { className: 'tbp-color-picker' },
                React.createElement('div', {
                    className: 'tbp-color-preview',
                    style: { backgroundColor: localColor },
                    onClick: () => setIsOpen(!isOpen)
                }),
                React.createElement('input', {
                    type: 'text',
                    className: 'tbp-color-input',
                    value: localColor,
                    onChange: (e) => {
                        setLocalColor(e.target.value);
                        onChange(e.target.value);
                    }
                }),
                isOpen && React.createElement('div', { className: 'tbp-color-popover' },
                    React.createElement('input', {
                        type: 'color',
                        value: localColor,
                        onChange: (e) => {
                            setLocalColor(e.target.value);
                            onChange(e.target.value);
                        }
                    }),
                    React.createElement('div', { className: 'tbp-color-presets' },
                        presetColors.map(color =>
                            React.createElement('div', {
                                key: color,
                                className: 'tbp-color-preset',
                                style: { backgroundColor: color },
                                onClick: () => {
                                    setLocalColor(color);
                                    onChange(color);
                                }
                            })
                        )
                    )
                )
            )
        );
    };

    // Select Control
    const SelectControl = ({ label, value, onChange, options }) => {
        return React.createElement('div', { className: 'tbp-control tbp-control-select' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('select', {
                className: 'tbp-control-select-input',
                value: value || '',
                onChange: (e) => onChange(e.target.value)
            },
                options.map(opt =>
                    React.createElement('option', { key: opt.value, value: opt.value }, opt.label)
                )
            )
        );
    };

    // Switcher Control
    const SwitcherControl = ({ label, value, onChange }) => {
        return React.createElement('div', { className: 'tbp-control tbp-control-switcher' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('div', {
                className: `tbp-switcher ${value ? 'active' : ''}`,
                onClick: () => onChange(!value)
            },
                React.createElement('div', { className: 'tbp-switcher-toggle' })
            )
        );
    };

    // Media Control
    const MediaControl = ({ label, value, onChange }) => {
        const openMediaLibrary = () => {
            const frame = wp.media({
                title: label,
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', () => {
                const attachment = frame.state().get('selection').first().toJSON();
                onChange({
                    id: attachment.id,
                    url: attachment.url,
                    alt: attachment.alt
                });
            });

            frame.open();
        };

        return React.createElement('div', { className: 'tbp-control tbp-control-media' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('div', { className: 'tbp-media-preview' },
                value && value.url ?
                    React.createElement('img', { src: value.url, alt: value.alt || '' }) :
                    React.createElement('div', { className: 'tbp-media-placeholder' }, 'No image selected')
            ),
            React.createElement('div', { className: 'tbp-media-actions' },
                React.createElement('button', {
                    type: 'button',
                    className: 'tbp-button tbp-button-secondary',
                    onClick: openMediaLibrary
                }, value && value.url ? 'Change' : 'Select'),
                value && value.url && React.createElement('button', {
                    type: 'button',
                    className: 'tbp-button tbp-button-text',
                    onClick: () => onChange(null)
                }, 'Remove')
            )
        );
    };

    // Typography Control
    const TypographyControl = ({ label, value, onChange }) => {
        const defaultValue = {
            family: 'Default',
            size: 16,
            weight: '400',
            transform: 'none',
            style: 'normal',
            decoration: 'none',
            lineHeight: 1.5,
            letterSpacing: 0
        };

        const typography = { ...defaultValue, ...value };

        const updateTypography = (key, newValue) => {
            onChange({ ...typography, [key]: newValue });
        };

        const fontFamilies = [
            { value: 'Default', label: 'Default' },
            { value: 'Arial', label: 'Arial' },
            { value: 'Helvetica', label: 'Helvetica' },
            { value: 'Georgia', label: 'Georgia' },
            { value: 'Times New Roman', label: 'Times New Roman' },
            { value: 'Roboto', label: 'Roboto' },
            { value: 'Open Sans', label: 'Open Sans' },
            { value: 'Lato', label: 'Lato' },
            { value: 'Montserrat', label: 'Montserrat' },
            { value: 'Poppins', label: 'Poppins' }
        ];

        const fontWeights = [
            { value: '100', label: 'Thin' },
            { value: '200', label: 'Extra Light' },
            { value: '300', label: 'Light' },
            { value: '400', label: 'Normal' },
            { value: '500', label: 'Medium' },
            { value: '600', label: 'Semi Bold' },
            { value: '700', label: 'Bold' },
            { value: '800', label: 'Extra Bold' },
            { value: '900', label: 'Black' }
        ];

        return React.createElement('div', { className: 'tbp-control tbp-control-typography' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('div', { className: 'tbp-typography-controls' },
                React.createElement(SelectControl, {
                    label: 'Family',
                    value: typography.family,
                    onChange: (v) => updateTypography('family', v),
                    options: fontFamilies
                }),
                React.createElement(SliderControl, {
                    label: 'Size',
                    value: typography.size,
                    onChange: (v) => updateTypography('size', v),
                    min: 8,
                    max: 200
                }),
                React.createElement(SelectControl, {
                    label: 'Weight',
                    value: typography.weight,
                    onChange: (v) => updateTypography('weight', v),
                    options: fontWeights
                }),
                React.createElement(SliderControl, {
                    label: 'Line Height',
                    value: typography.lineHeight,
                    onChange: (v) => updateTypography('lineHeight', v),
                    min: 0.5,
                    max: 3,
                    step: 0.1,
                    unit: 'em'
                }),
                React.createElement(SliderControl, {
                    label: 'Letter Spacing',
                    value: typography.letterSpacing,
                    onChange: (v) => updateTypography('letterSpacing', v),
                    min: -5,
                    max: 10,
                    step: 0.1
                })
            )
        );
    };

    // Dimensions Control (Padding/Margin)
    const DimensionsControl = ({ label, value, onChange }) => {
        const [isLinked, setIsLinked] = useState(true);
        const defaultValue = { top: 0, right: 0, bottom: 0, left: 0, unit: 'px' };
        const dimensions = { ...defaultValue, ...value };

        const updateDimension = (side, newValue) => {
            if (isLinked) {
                onChange({
                    ...dimensions,
                    top: newValue,
                    right: newValue,
                    bottom: newValue,
                    left: newValue
                });
            } else {
                onChange({ ...dimensions, [side]: newValue });
            }
        };

        return React.createElement('div', { className: 'tbp-control tbp-control-dimensions' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('div', { className: 'tbp-dimensions-inputs' },
                ['top', 'right', 'bottom', 'left'].map(side =>
                    React.createElement('div', { key: side, className: 'tbp-dimension-input' },
                        React.createElement('input', {
                            type: 'number',
                            value: dimensions[side],
                            onChange: (e) => updateDimension(side, parseFloat(e.target.value) || 0)
                        }),
                        React.createElement('span', { className: 'tbp-dimension-label' }, side.charAt(0).toUpperCase())
                    )
                ),
                React.createElement('button', {
                    type: 'button',
                    className: `tbp-dimensions-link ${isLinked ? 'linked' : ''}`,
                    onClick: () => setIsLinked(!isLinked),
                    title: isLinked ? 'Unlink values' : 'Link values'
                }, isLinked ? '🔗' : '⛓️‍💥')
            )
        );
    };

    // Border Control
    const BorderControl = ({ label, value, onChange }) => {
        const defaultValue = {
            width: 0,
            style: 'solid',
            color: '#000000',
            radius: { top: 0, right: 0, bottom: 0, left: 0 }
        };
        const border = { ...defaultValue, ...value };

        const updateBorder = (key, newValue) => {
            onChange({ ...border, [key]: newValue });
        };

        const borderStyles = [
            { value: 'none', label: 'None' },
            { value: 'solid', label: 'Solid' },
            { value: 'dashed', label: 'Dashed' },
            { value: 'dotted', label: 'Dotted' },
            { value: 'double', label: 'Double' }
        ];

        return React.createElement('div', { className: 'tbp-control tbp-control-border' },
            React.createElement('label', { className: 'tbp-control-label' }, label),
            React.createElement('div', { className: 'tbp-border-controls' },
                React.createElement(SliderControl, {
                    label: 'Width',
                    value: border.width,
                    onChange: (v) => updateBorder('width', v),
                    min: 0,
                    max: 20
                }),
                React.createElement(SelectControl, {
                    label: 'Style',
                    value: border.style,
                    onChange: (v) => updateBorder('style', v),
                    options: borderStyles
                }),
                React.createElement(ColorControl, {
                    label: 'Color',
                    value: border.color,
                    onChange: (v) => updateBorder('color', v)
                }),
                React.createElement(DimensionsControl, {
                    label: 'Border Radius',
                    value: border.radius,
                    onChange: (v) => updateBorder('radius', v)
                })
            )
        );
    };

    // Box Shadow Control
    const BoxShadowControl = ({ label, value, onChange }) => {
        const defaultValue = {
            enabled: false,
            horizontal: 0,
            vertical: 4,
            blur: 10,
            spread: 0,
            color: 'rgba(0,0,0,0.2)',
            inset: false
        };
        const shadow = { ...defaultValue, ...value };

        const updateShadow = (key, newValue) => {
            onChange({ ...shadow, [key]: newValue });
        };

        return React.createElement('div', { className: 'tbp-control tbp-control-box-shadow' },
            React.createElement(SwitcherControl, {
                label: label,
                value: shadow.enabled,
                onChange: (v) => updateShadow('enabled', v)
            }),
            shadow.enabled && React.createElement('div', { className: 'tbp-shadow-controls' },
                React.createElement(SliderControl, {
                    label: 'Horizontal',
                    value: shadow.horizontal,
                    onChange: (v) => updateShadow('horizontal', v),
                    min: -50,
                    max: 50
                }),
                React.createElement(SliderControl, {
                    label: 'Vertical',
                    value: shadow.vertical,
                    onChange: (v) => updateShadow('vertical', v),
                    min: -50,
                    max: 50
                }),
                React.createElement(SliderControl, {
                    label: 'Blur',
                    value: shadow.blur,
                    onChange: (v) => updateShadow('blur', v),
                    min: 0,
                    max: 100
                }),
                React.createElement(SliderControl, {
                    label: 'Spread',
                    value: shadow.spread,
                    onChange: (v) => updateShadow('spread', v),
                    min: -50,
                    max: 50
                }),
                React.createElement(ColorControl, {
                    label: 'Color',
                    value: shadow.color,
                    onChange: (v) => updateShadow('color', v)
                }),
                React.createElement(SwitcherControl, {
                    label: 'Inset',
                    value: shadow.inset,
                    onChange: (v) => updateShadow('inset', v)
                })
            )
        );
    };

    // ============================================
    // COMPONENTS - WIDGETS PANEL
    // ============================================

    const WidgetsPanel = () => {
        const [widgets, setWidgets] = useState([]);
        const [searchTerm, setSearchTerm] = useState('');
        const [activeCategory, setActiveCategory] = useState('all');

        useEffect(() => {
            // Load widgets from config
            setWidgets(tbpEditor.widgets || []);
        }, []);

        const categories = useMemo(() => {
            const cats = new Set(['all']);
            widgets.forEach(w => cats.add(w.category));
            return Array.from(cats);
        }, [widgets]);

        const filteredWidgets = useMemo(() => {
            return widgets.filter(w => {
                const matchesSearch = w.name.toLowerCase().includes(searchTerm.toLowerCase());
                const matchesCategory = activeCategory === 'all' || w.category === activeCategory;
                return matchesSearch && matchesCategory;
            });
        }, [widgets, searchTerm, activeCategory]);

        const handleDragStart = (e, widget) => {
            e.dataTransfer.setData('widget', JSON.stringify(widget));
            e.dataTransfer.effectAllowed = 'copy';
        };

        return React.createElement('div', { className: 'tbp-widgets-panel' },
            React.createElement('div', { className: 'tbp-widgets-search' },
                React.createElement('input', {
                    type: 'text',
                    placeholder: 'Search widgets...',
                    value: searchTerm,
                    onChange: (e) => setSearchTerm(e.target.value)
                })
            ),
            React.createElement('div', { className: 'tbp-widgets-categories' },
                categories.map(cat =>
                    React.createElement('button', {
                        key: cat,
                        className: `tbp-category-tab ${activeCategory === cat ? 'active' : ''}`,
                        onClick: () => setActiveCategory(cat)
                    }, cat.charAt(0).toUpperCase() + cat.slice(1))
                )
            ),
            React.createElement('div', { className: 'tbp-widgets-grid' },
                filteredWidgets.map(widget =>
                    React.createElement('div', {
                        key: widget.id,
                        className: 'tbp-widget-item',
                        draggable: true,
                        onDragStart: (e) => handleDragStart(e, widget)
                    },
                        React.createElement('div', { className: 'tbp-widget-icon' },
                            React.createElement('i', { className: widget.icon })
                        ),
                        React.createElement('div', { className: 'tbp-widget-name' }, widget.name)
                    )
                )
            )
        );
    };

    // ============================================
    // COMPONENTS - ELEMENT SETTINGS PANEL
    // ============================================

    const ElementSettingsPanel = ({ element, onUpdate, onClose }) => {
        const [activeTab, setActiveTab] = useState('content');
        const [settings, setSettings] = useState(element?.settings || {});

        useEffect(() => {
            setSettings(element?.settings || {});
        }, [element]);

        const updateSetting = useCallback((key, value) => {
            const newSettings = { ...settings, [key]: value };
            setSettings(newSettings);
            onUpdate({ settings: newSettings });
        }, [settings, onUpdate]);

        if (!element) {
            return React.createElement('div', { className: 'tbp-settings-panel tbp-settings-empty' },
                React.createElement('p', null, 'Select an element to edit its settings')
            );
        }

        const tabs = [
            { id: 'content', label: 'Content', icon: '📝' },
            { id: 'style', label: 'Style', icon: '🎨' },
            { id: 'advanced', label: 'Advanced', icon: '⚙️' }
        ];

        const renderContentTab = () => {
            const widgetType = element.type;

            switch (widgetType) {
                case 'heading':
                    return React.createElement(React.Fragment, null,
                        React.createElement(TextControl, {
                            label: 'Title',
                            value: settings.title,
                            onChange: (v) => updateSetting('title', v)
                        }),
                        React.createElement(SelectControl, {
                            label: 'HTML Tag',
                            value: settings.tag || 'h2',
                            onChange: (v) => updateSetting('tag', v),
                            options: [
                                { value: 'h1', label: 'H1' },
                                { value: 'h2', label: 'H2' },
                                { value: 'h3', label: 'H3' },
                                { value: 'h4', label: 'H4' },
                                { value: 'h5', label: 'H5' },
                                { value: 'h6', label: 'H6' },
                                { value: 'p', label: 'P' },
                                { value: 'span', label: 'Span' },
                                { value: 'div', label: 'Div' }
                            ]
                        }),
                        React.createElement(SelectControl, {
                            label: 'Alignment',
                            value: settings.align || 'left',
                            onChange: (v) => updateSetting('align', v),
                            options: [
                                { value: 'left', label: 'Left' },
                                { value: 'center', label: 'Center' },
                                { value: 'right', label: 'Right' },
                                { value: 'justify', label: 'Justify' }
                            ]
                        }),
                        React.createElement(TextControl, {
                            label: 'Link URL',
                            value: settings.link,
                            onChange: (v) => updateSetting('link', v)
                        })
                    );

                case 'text-editor':
                    return React.createElement(React.Fragment, null,
                        React.createElement(TextareaControl, {
                            label: 'Content',
                            value: settings.content,
                            onChange: (v) => updateSetting('content', v),
                            rows: 8
                        })
                    );

                case 'button':
                    return React.createElement(React.Fragment, null,
                        React.createElement(TextControl, {
                            label: 'Text',
                            value: settings.text,
                            onChange: (v) => updateSetting('text', v)
                        }),
                        React.createElement(TextControl, {
                            label: 'Link',
                            value: settings.link,
                            onChange: (v) => updateSetting('link', v)
                        }),
                        React.createElement(SelectControl, {
                            label: 'Size',
                            value: settings.size || 'medium',
                            onChange: (v) => updateSetting('size', v),
                            options: [
                                { value: 'xs', label: 'Extra Small' },
                                { value: 'sm', label: 'Small' },
                                { value: 'medium', label: 'Medium' },
                                { value: 'lg', label: 'Large' },
                                { value: 'xl', label: 'Extra Large' }
                            ]
                        }),
                        React.createElement(SelectControl, {
                            label: 'Alignment',
                            value: settings.align || 'left',
                            onChange: (v) => updateSetting('align', v),
                            options: [
                                { value: 'left', label: 'Left' },
                                { value: 'center', label: 'Center' },
                                { value: 'right', label: 'Right' },
                                { value: 'justify', label: 'Stretch' }
                            ]
                        })
                    );

                case 'image':
                    return React.createElement(React.Fragment, null,
                        React.createElement(MediaControl, {
                            label: 'Image',
                            value: settings.image,
                            onChange: (v) => updateSetting('image', v)
                        }),
                        React.createElement(SelectControl, {
                            label: 'Size',
                            value: settings.size || 'full',
                            onChange: (v) => updateSetting('size', v),
                            options: [
                                { value: 'thumbnail', label: 'Thumbnail' },
                                { value: 'medium', label: 'Medium' },
                                { value: 'large', label: 'Large' },
                                { value: 'full', label: 'Full' }
                            ]
                        }),
                        React.createElement(TextControl, {
                            label: 'Alt Text',
                            value: settings.alt,
                            onChange: (v) => updateSetting('alt', v)
                        }),
                        React.createElement(TextControl, {
                            label: 'Link',
                            value: settings.link,
                            onChange: (v) => updateSetting('link', v)
                        }),
                        React.createElement(SelectControl, {
                            label: 'Alignment',
                            value: settings.align || 'left',
                            onChange: (v) => updateSetting('align', v),
                            options: [
                                { value: 'left', label: 'Left' },
                                { value: 'center', label: 'Center' },
                                { value: 'right', label: 'Right' }
                            ]
                        })
                    );

                case 'section':
                case 'container':
                    return React.createElement(React.Fragment, null,
                        React.createElement(SelectControl, {
                            label: 'Content Width',
                            value: settings.contentWidth || 'boxed',
                            onChange: (v) => updateSetting('contentWidth', v),
                            options: [
                                { value: 'boxed', label: 'Boxed' },
                                { value: 'full', label: 'Full Width' }
                            ]
                        }),
                        React.createElement(SliderControl, {
                            label: 'Min Height',
                            value: settings.minHeight || 0,
                            onChange: (v) => updateSetting('minHeight', v),
                            min: 0,
                            max: 1000
                        }),
                        React.createElement(SelectControl, {
                            label: 'HTML Tag',
                            value: settings.tag || 'section',
                            onChange: (v) => updateSetting('tag', v),
                            options: [
                                { value: 'section', label: 'Section' },
                                { value: 'div', label: 'Div' },
                                { value: 'header', label: 'Header' },
                                { value: 'footer', label: 'Footer' },
                                { value: 'main', label: 'Main' },
                                { value: 'article', label: 'Article' },
                                { value: 'aside', label: 'Aside' }
                            ]
                        })
                    );

                default:
                    return React.createElement('p', null, 'No content settings available for this widget');
            }
        };

        const renderStyleTab = () => {
            return React.createElement(React.Fragment, null,
                React.createElement(TypographyControl, {
                    label: 'Typography',
                    value: settings.typography,
                    onChange: (v) => updateSetting('typography', v)
                }),
                React.createElement(ColorControl, {
                    label: 'Text Color',
                    value: settings.textColor,
                    onChange: (v) => updateSetting('textColor', v)
                }),
                React.createElement(ColorControl, {
                    label: 'Background Color',
                    value: settings.backgroundColor,
                    onChange: (v) => updateSetting('backgroundColor', v)
                }),
                React.createElement(DimensionsControl, {
                    label: 'Padding',
                    value: settings.padding,
                    onChange: (v) => updateSetting('padding', v)
                }),
                React.createElement(DimensionsControl, {
                    label: 'Margin',
                    value: settings.margin,
                    onChange: (v) => updateSetting('margin', v)
                }),
                React.createElement(BorderControl, {
                    label: 'Border',
                    value: settings.border,
                    onChange: (v) => updateSetting('border', v)
                }),
                React.createElement(BoxShadowControl, {
                    label: 'Box Shadow',
                    value: settings.boxShadow,
                    onChange: (v) => updateSetting('boxShadow', v)
                })
            );
        };

        const renderAdvancedTab = () => {
            return React.createElement(React.Fragment, null,
                React.createElement(TextControl, {
                    label: 'CSS ID',
                    value: settings.cssId,
                    onChange: (v) => updateSetting('cssId', v)
                }),
                React.createElement(TextControl, {
                    label: 'CSS Classes',
                    value: settings.cssClasses,
                    onChange: (v) => updateSetting('cssClasses', v)
                }),
                React.createElement(TextareaControl, {
                    label: 'Custom CSS',
                    value: settings.customCss,
                    onChange: (v) => updateSetting('customCss', v),
                    rows: 6
                }),
                React.createElement(SelectControl, {
                    label: 'Entrance Animation',
                    value: settings.animation || 'none',
                    onChange: (v) => updateSetting('animation', v),
                    options: [
                        { value: 'none', label: 'None' },
                        { value: 'fade-in', label: 'Fade In' },
                        { value: 'fade-in-up', label: 'Fade In Up' },
                        { value: 'fade-in-down', label: 'Fade In Down' },
                        { value: 'fade-in-left', label: 'Fade In Left' },
                        { value: 'fade-in-right', label: 'Fade In Right' },
                        { value: 'zoom-in', label: 'Zoom In' },
                        { value: 'bounce-in', label: 'Bounce In' },
                        { value: 'slide-in-up', label: 'Slide In Up' },
                        { value: 'slide-in-down', label: 'Slide In Down' }
                    ]
                }),
                React.createElement(SliderControl, {
                    label: 'Animation Duration',
                    value: settings.animationDuration || 1000,
                    onChange: (v) => updateSetting('animationDuration', v),
                    min: 100,
                    max: 3000,
                    step: 100,
                    unit: 'ms'
                }),
                React.createElement(SliderControl, {
                    label: 'Animation Delay',
                    value: settings.animationDelay || 0,
                    onChange: (v) => updateSetting('animationDelay', v),
                    min: 0,
                    max: 3000,
                    step: 100,
                    unit: 'ms'
                }),
                React.createElement('h4', null, 'Responsive'),
                React.createElement(SwitcherControl, {
                    label: 'Hide on Desktop',
                    value: settings.hideDesktop,
                    onChange: (v) => updateSetting('hideDesktop', v)
                }),
                React.createElement(SwitcherControl, {
                    label: 'Hide on Tablet',
                    value: settings.hideTablet,
                    onChange: (v) => updateSetting('hideTablet', v)
                }),
                React.createElement(SwitcherControl, {
                    label: 'Hide on Mobile',
                    value: settings.hideMobile,
                    onChange: (v) => updateSetting('hideMobile', v)
                })
            );
        };

        return React.createElement('div', { className: 'tbp-settings-panel' },
            React.createElement('div', { className: 'tbp-settings-header' },
                React.createElement('h3', null, element.type),
                React.createElement('button', {
                    className: 'tbp-settings-close',
                    onClick: onClose
                }, '×')
            ),
            React.createElement('div', { className: 'tbp-settings-tabs' },
                tabs.map(tab =>
                    React.createElement('button', {
                        key: tab.id,
                        className: `tbp-settings-tab ${activeTab === tab.id ? 'active' : ''}`,
                        onClick: () => setActiveTab(tab.id)
                    }, tab.icon, ' ', tab.label)
                )
            ),
            React.createElement('div', { className: 'tbp-settings-content' },
                activeTab === 'content' && renderContentTab(),
                activeTab === 'style' && renderStyleTab(),
                activeTab === 'advanced' && renderAdvancedTab()
            )
        );
    };

    // ============================================
    // COMPONENTS - CANVAS ELEMENT
    // ============================================

    const CanvasElement = ({ element, isSelected, onSelect, onUpdate, onDelete, onDuplicate }) => {
        const elementRef = useRef(null);
        const [isDragging, setIsDragging] = useState(false);

        const handleClick = (e) => {
            e.stopPropagation();
            onSelect(element.id);
        };

        const handleDragStart = (e) => {
            setIsDragging(true);
            e.dataTransfer.setData('element-id', element.id);
            e.dataTransfer.effectAllowed = 'move';
        };

        const handleDragEnd = () => {
            setIsDragging(false);
        };

        const renderElementContent = () => {
            const { settings } = element;

            switch (element.type) {
                case 'heading':
                    const HeadingTag = settings?.tag || 'h2';
                    return React.createElement(HeadingTag, {
                        style: {
                            textAlign: settings?.align || 'left',
                            color: settings?.textColor
                        }
                    }, settings?.title || 'Add Your Heading Text Here');

                case 'text-editor':
                    return React.createElement('div', {
                        className: 'tbp-text-editor-content',
                        dangerouslySetInnerHTML: { __html: settings?.content || '<p>Add your content here...</p>' }
                    });

                case 'button':
                    return React.createElement('div', {
                        style: { textAlign: settings?.align || 'left' }
                    },
                        React.createElement('a', {
                            href: '#',
                            className: `tbp-button tbp-button-${settings?.size || 'medium'}`,
                            style: {
                                backgroundColor: settings?.backgroundColor,
                                color: settings?.textColor
                            }
                        }, settings?.text || 'Click Here')
                    );

                case 'image':
                    if (settings?.image?.url) {
                        return React.createElement('div', {
                            style: { textAlign: settings?.align || 'left' }
                        },
                            React.createElement('img', {
                                src: settings.image.url,
                                alt: settings?.alt || '',
                                style: { maxWidth: '100%' }
                            })
                        );
                    }
                    return React.createElement('div', { className: 'tbp-image-placeholder' },
                        'Click to select an image'
                    );

                case 'section':
                case 'container':
                    return React.createElement('div', {
                        className: 'tbp-section-content',
                        style: {
                            minHeight: settings?.minHeight ? `${settings.minHeight}px` : '100px'
                        }
                    },
                        element.children?.length > 0 ?
                            element.children.map(child =>
                                React.createElement(CanvasElement, {
                                    key: child.id,
                                    element: child,
                                    isSelected: false,
                                    onSelect,
                                    onUpdate,
                                    onDelete,
                                    onDuplicate
                                })
                            ) :
                            React.createElement('div', { className: 'tbp-empty-section' },
                                'Drag widgets here'
                            )
                    );

                case 'columns':
                    const cols = settings?.columns || 2;
                    return React.createElement('div', {
                        className: 'tbp-columns',
                        style: {
                            display: 'grid',
                            gridTemplateColumns: `repeat(${cols}, 1fr)`,
                            gap: '20px'
                        }
                    },
                        [...Array(cols)].map((_, i) =>
                            React.createElement('div', {
                                key: i,
                                className: 'tbp-column'
                            }, 'Column ', i + 1)
                        )
                    );

                case 'spacer':
                    return React.createElement('div', {
                        className: 'tbp-spacer',
                        style: { height: `${settings?.space || 50}px` }
                    });

                case 'divider':
                    return React.createElement('hr', {
                        className: 'tbp-divider',
                        style: {
                            borderColor: settings?.color || '#ccc',
                            borderWidth: `${settings?.weight || 1}px`
                        }
                    });

                default:
                    return React.createElement('div', { className: 'tbp-widget-placeholder' },
                        element.type
                    );
            }
        };

        const elementStyle = {
            padding: element.settings?.padding ?
                `${element.settings.padding.top}px ${element.settings.padding.right}px ${element.settings.padding.bottom}px ${element.settings.padding.left}px` :
                undefined,
            margin: element.settings?.margin ?
                `${element.settings.margin.top}px ${element.settings.margin.right}px ${element.settings.margin.bottom}px ${element.settings.margin.left}px` :
                undefined,
            backgroundColor: element.settings?.backgroundColor,
            border: element.settings?.border?.width ?
                `${element.settings.border.width}px ${element.settings.border.style} ${element.settings.border.color}` :
                undefined,
            borderRadius: element.settings?.border?.radius ?
                `${element.settings.border.radius.top}px ${element.settings.border.radius.right}px ${element.settings.border.radius.bottom}px ${element.settings.border.radius.left}px` :
                undefined,
            boxShadow: element.settings?.boxShadow?.enabled ?
                `${element.settings.boxShadow.inset ? 'inset ' : ''}${element.settings.boxShadow.horizontal}px ${element.settings.boxShadow.vertical}px ${element.settings.boxShadow.blur}px ${element.settings.boxShadow.spread}px ${element.settings.boxShadow.color}` :
                undefined
        };

        return React.createElement('div', {
            ref: elementRef,
            className: `tbp-canvas-element tbp-element-${element.type} ${isSelected ? 'selected' : ''} ${isDragging ? 'dragging' : ''}`,
            style: elementStyle,
            onClick: handleClick,
            draggable: true,
            onDragStart: handleDragStart,
            onDragEnd: handleDragEnd,
            'data-element-id': element.id
        },
            React.createElement('div', { className: 'tbp-element-overlay' },
                React.createElement('div', { className: 'tbp-element-actions' },
                    React.createElement('button', {
                        className: 'tbp-element-action',
                        onClick: (e) => { e.stopPropagation(); onDuplicate(element.id); },
                        title: 'Duplicate'
                    }, '📋'),
                    React.createElement('button', {
                        className: 'tbp-element-action tbp-element-action-delete',
                        onClick: (e) => { e.stopPropagation(); onDelete(element.id); },
                        title: 'Delete'
                    }, '🗑️')
                ),
                React.createElement('div', { className: 'tbp-element-label' }, element.type)
            ),
            React.createElement('div', { className: 'tbp-element-content' },
                renderElementContent()
            )
        );
    };

    // ============================================
    // COMPONENTS - CANVAS
    // ============================================

    const Canvas = ({ elements, selectedElement, currentDevice, onSelectElement, onUpdateElement, onDeleteElement, onDuplicateElement, onAddElement, onMoveElement }) => {
        const canvasRef = useRef(null);
        const [dragOverIndex, setDragOverIndex] = useState(null);

        const handleDragOver = (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        };

        const handleDrop = (e) => {
            e.preventDefault();

            const widgetData = e.dataTransfer.getData('widget');
            const elementId = e.dataTransfer.getData('element-id');

            if (widgetData) {
                // Adding new widget
                const widget = JSON.parse(widgetData);
                const newElement = {
                    id: generateId(),
                    type: widget.id,
                    settings: widget.defaultSettings || {}
                };
                onAddElement(newElement);
            } else if (elementId) {
                // Moving existing element
                const fromIndex = elements.findIndex(el => el.id === elementId);
                if (fromIndex !== -1 && dragOverIndex !== null && fromIndex !== dragOverIndex) {
                    onMoveElement(fromIndex, dragOverIndex);
                }
            }

            setDragOverIndex(null);
        };

        const handleElementDragOver = (e, index) => {
            e.preventDefault();
            e.stopPropagation();
            setDragOverIndex(index);
        };

        const handleCanvasClick = (e) => {
            if (e.target === canvasRef.current || e.target.classList.contains('tbp-canvas-inner')) {
                onSelectElement(null);
            }
        };

        const deviceClass = `tbp-device-${currentDevice}`;

        return React.createElement('div', {
            ref: canvasRef,
            className: `tbp-canvas ${deviceClass}`,
            onDragOver: handleDragOver,
            onDrop: handleDrop,
            onClick: handleCanvasClick
        },
            React.createElement('div', { className: 'tbp-canvas-inner' },
                elements.length === 0 ?
                    React.createElement('div', { className: 'tbp-canvas-empty' },
                        React.createElement('div', { className: 'tbp-canvas-empty-icon' }, '📦'),
                        React.createElement('h3', null, 'Drag & Drop Widgets Here'),
                        React.createElement('p', null, 'Pick a widget from the left panel and drop it here to start building')
                    ) :
                    elements.map((element, index) =>
                        React.createElement('div', {
                            key: element.id,
                            className: `tbp-element-wrapper ${dragOverIndex === index ? 'drag-over' : ''}`,
                            onDragOver: (e) => handleElementDragOver(e, index)
                        },
                            React.createElement(CanvasElement, {
                                element,
                                isSelected: selectedElement === element.id,
                                onSelect: onSelectElement,
                                onUpdate: (data) => onUpdateElement(element.id, data),
                                onDelete: onDeleteElement,
                                onDuplicate: onDuplicateElement
                            })
                        )
                    )
            )
        );
    };

    // ============================================
    // COMPONENTS - HEADER
    // ============================================

    const EditorHeader = ({
        documentTitle,
        isSaving,
        isDirty,
        currentDevice,
        onDeviceChange,
        onSave,
        onPreview,
        onUndo,
        onRedo,
        canUndo,
        canRedo
    }) => {
        const devices = [
            { id: 'desktop', icon: '🖥️', label: 'Desktop' },
            { id: 'tablet', icon: '📱', label: 'Tablet' },
            { id: 'mobile', icon: '📱', label: 'Mobile' }
        ];

        return React.createElement('div', { className: 'tbp-editor-header' },
            React.createElement('div', { className: 'tbp-header-left' },
                React.createElement('a', {
                    href: tbpEditor.adminUrl,
                    className: 'tbp-header-logo'
                }, '← Back'),
                React.createElement('div', { className: 'tbp-header-title' },
                    React.createElement('h1', null, documentTitle || 'Untitled'),
                    isDirty && React.createElement('span', { className: 'tbp-unsaved-indicator' }, '●')
                )
            ),
            React.createElement('div', { className: 'tbp-header-center' },
                React.createElement('div', { className: 'tbp-history-buttons' },
                    React.createElement('button', {
                        className: 'tbp-header-button',
                        onClick: onUndo,
                        disabled: !canUndo,
                        title: 'Undo (Ctrl+Z)'
                    }, '↩️'),
                    React.createElement('button', {
                        className: 'tbp-header-button',
                        onClick: onRedo,
                        disabled: !canRedo,
                        title: 'Redo (Ctrl+Shift+Z)'
                    }, '↪️')
                ),
                React.createElement('div', { className: 'tbp-responsive-buttons' },
                    devices.map(device =>
                        React.createElement('button', {
                            key: device.id,
                            className: `tbp-device-button ${currentDevice === device.id ? 'active' : ''}`,
                            onClick: () => onDeviceChange(device.id),
                            title: device.label
                        }, device.icon)
                    )
                )
            ),
            React.createElement('div', { className: 'tbp-header-right' },
                React.createElement('button', {
                    className: 'tbp-header-button',
                    onClick: onPreview,
                    title: 'Preview'
                }, '👁️ Preview'),
                React.createElement('button', {
                    className: 'tbp-header-button tbp-button-primary',
                    onClick: onSave,
                    disabled: isSaving
                }, isSaving ? 'Saving...' : 'Save')
            )
        );
    };

    // ============================================
    // COMPONENTS - MAIN EDITOR
    // ============================================

    const Editor = () => {
        const [state, dispatch] = useReducer(editorReducer, {
            elements: [],
            selectedElement: null,
            currentDevice: 'desktop',
            activePanel: 'widgets',
            isSaving: false,
            isPreview: false,
            isDirty: false
        });

        const [history, historyDispatch] = useReducer(historyReducer, {
            past: [],
            present: [],
            future: []
        });

        const [documentTitle, setDocumentTitle] = useState('');
        const documentId = tbpEditor.documentId;

        // Load document on mount
        useEffect(() => {
            const loadDocument = async () => {
                try {
                    const doc = await API.getDocument(documentId);
                    setDocumentTitle(doc.title);
                    dispatch({ type: 'SET_ELEMENTS', payload: doc.content || [] });
                    historyDispatch({ type: 'RESET', payload: doc.content || [] });
                } catch (error) {
                    console.error('Failed to load document:', error);
                }
            };
            loadDocument();
        }, [documentId]);

        // Update history when elements change
        useEffect(() => {
            if (state.isDirty) {
                historyDispatch({ type: 'PUSH', payload: state.elements });
            }
        }, [state.elements]);

        // Keyboard shortcuts
        useEffect(() => {
            const handleKeyDown = (e) => {
                // Ctrl/Cmd + S to save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    handleSave();
                }
                // Ctrl/Cmd + Z to undo
                if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    handleUndo();
                }
                // Ctrl/Cmd + Shift + Z or Ctrl/Cmd + Y to redo
                if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) {
                    e.preventDefault();
                    handleRedo();
                }
                // Delete selected element
                if (e.key === 'Delete' && state.selectedElement) {
                    dispatch({ type: 'DELETE_ELEMENT', payload: state.selectedElement });
                    dispatch({ type: 'SET_DIRTY', payload: true });
                }
                // Escape to deselect
                if (e.key === 'Escape') {
                    dispatch({ type: 'SELECT_ELEMENT', payload: null });
                }
            };

            document.addEventListener('keydown', handleKeyDown);
            return () => document.removeEventListener('keydown', handleKeyDown);
        }, [state.selectedElement]);

        // Handlers
        const handleSave = async () => {
            dispatch({ type: 'SET_SAVING', payload: true });
            try {
                await API.saveDocument(documentId, {
                    content: state.elements
                });
                dispatch({ type: 'SET_DIRTY', payload: false });
            } catch (error) {
                console.error('Failed to save:', error);
                alert('Failed to save. Please try again.');
            }
            dispatch({ type: 'SET_SAVING', payload: false });
        };

        const handlePreview = () => {
            window.open(tbpEditor.previewUrl, '_blank');
        };

        const handleUndo = () => {
            if (history.past.length > 0) {
                historyDispatch({ type: 'UNDO' });
                dispatch({ type: 'SET_ELEMENTS', payload: history.past[history.past.length - 1] });
            }
        };

        const handleRedo = () => {
            if (history.future.length > 0) {
                historyDispatch({ type: 'REDO' });
                dispatch({ type: 'SET_ELEMENTS', payload: history.future[0] });
            }
        };

        const handleAddElement = (element) => {
            dispatch({ type: 'ADD_ELEMENT', payload: element });
            dispatch({ type: 'SELECT_ELEMENT', payload: element.id });
            dispatch({ type: 'SET_DIRTY', payload: true });
        };

        const handleUpdateElement = (id, data) => {
            dispatch({ type: 'UPDATE_ELEMENT', payload: { id, data } });
            dispatch({ type: 'SET_DIRTY', payload: true });
        };

        const handleDeleteElement = (id) => {
            dispatch({ type: 'DELETE_ELEMENT', payload: id });
            dispatch({ type: 'SET_DIRTY', payload: true });
        };

        const handleDuplicateElement = (id) => {
            dispatch({ type: 'DUPLICATE_ELEMENT', payload: id });
            dispatch({ type: 'SET_DIRTY', payload: true });
        };

        const handleMoveElement = (fromIndex, toIndex) => {
            dispatch({ type: 'MOVE_ELEMENT', payload: { fromIndex, toIndex } });
            dispatch({ type: 'SET_DIRTY', payload: true });
        };

        const selectedElementData = state.elements.find(el => el.id === state.selectedElement);

        // Panel content based on selection
        const renderLeftPanel = () => {
            if (state.selectedElement) {
                return React.createElement(ElementSettingsPanel, {
                    element: selectedElementData,
                    onUpdate: (data) => handleUpdateElement(state.selectedElement, data),
                    onClose: () => dispatch({ type: 'SELECT_ELEMENT', payload: null })
                });
            }
            return React.createElement(WidgetsPanel);
        };

        return React.createElement('div', { className: 'tbp-editor' },
            React.createElement(EditorHeader, {
                documentTitle,
                isSaving: state.isSaving,
                isDirty: state.isDirty,
                currentDevice: state.currentDevice,
                onDeviceChange: (device) => dispatch({ type: 'SET_DEVICE', payload: device }),
                onSave: handleSave,
                onPreview: handlePreview,
                onUndo: handleUndo,
                onRedo: handleRedo,
                canUndo: history.past.length > 0,
                canRedo: history.future.length > 0
            }),
            React.createElement('div', { className: 'tbp-editor-body' },
                React.createElement('div', { className: 'tbp-editor-panel tbp-editor-panel-left' },
                    renderLeftPanel()
                ),
                React.createElement('div', { className: 'tbp-editor-canvas-wrapper' },
                    React.createElement(Canvas, {
                        elements: state.elements,
                        selectedElement: state.selectedElement,
                        currentDevice: state.currentDevice,
                        onSelectElement: (id) => dispatch({ type: 'SELECT_ELEMENT', payload: id }),
                        onUpdateElement: handleUpdateElement,
                        onDeleteElement: handleDeleteElement,
                        onDuplicateElement: handleDuplicateElement,
                        onAddElement: handleAddElement,
                        onMoveElement: handleMoveElement
                    })
                )
            )
        );
    };

    // ============================================
    // INITIALIZATION
    // ============================================

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('tbp-editor-root');
        if (container) {
            const root = createRoot(container);
            root.render(React.createElement(Editor));
        }
    });

})(jQuery, React, ReactDOM);
