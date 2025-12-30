/**
 * Theme Builder Pro - Admin Application
 * Complete admin UI with React
 */

(function($) {
    'use strict';

    // ============================================
    // UTILITIES
    // ============================================

    const API = {
        nonce: tbpAdmin.nonce,
        ajaxUrl: tbpAdmin.ajaxUrl,

        async request(action, data = {}) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('nonce', this.nonce);

            Object.keys(data).forEach(key => {
                if (typeof data[key] === 'object') {
                    formData.append(key, JSON.stringify(data[key]));
                } else {
                    formData.append(key, data[key]);
                }
            });

            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            return response.json();
        }
    };

    // ============================================
    // TEMPLATES PAGE
    // ============================================

    const TemplatesPage = {
        container: null,
        templates: [],
        currentFilter: 'all',

        init() {
            this.container = document.getElementById('tbp-templates-app');
            if (!this.container) return;

            this.render();
            this.loadTemplates();
        },

        async loadTemplates() {
            const result = await API.request('tbp_get_templates');
            if (result.success) {
                this.templates = result.data;
                this.render();
            }
        },

        render() {
            const filteredTemplates = this.currentFilter === 'all'
                ? this.templates
                : this.templates.filter(t => t.type === this.currentFilter);

            this.container.innerHTML = `
                <div class="tbp-templates-header">
                    <div class="tbp-templates-filters">
                        <button class="tbp-filter-btn ${this.currentFilter === 'all' ? 'active' : ''}" data-filter="all">All</button>
                        <button class="tbp-filter-btn ${this.currentFilter === 'header' ? 'active' : ''}" data-filter="header">Headers</button>
                        <button class="tbp-filter-btn ${this.currentFilter === 'footer' ? 'active' : ''}" data-filter="footer">Footers</button>
                        <button class="tbp-filter-btn ${this.currentFilter === 'single' ? 'active' : ''}" data-filter="single">Single</button>
                        <button class="tbp-filter-btn ${this.currentFilter === 'archive' ? 'active' : ''}" data-filter="archive">Archive</button>
                        <button class="tbp-filter-btn ${this.currentFilter === 'page' ? 'active' : ''}" data-filter="page">Pages</button>
                    </div>
                    <button class="tbp-btn tbp-btn-primary" id="tbp-new-template">
                        <span class="dashicons dashicons-plus-alt2"></span> New Template
                    </button>
                </div>

                <div class="tbp-templates-grid">
                    ${filteredTemplates.length === 0 ? `
                        <div class="tbp-empty-state">
                            <div class="tbp-empty-icon">📄</div>
                            <h3>No templates yet</h3>
                            <p>Create your first template to get started</p>
                            <button class="tbp-btn tbp-btn-primary" id="tbp-create-first">Create Template</button>
                        </div>
                    ` : filteredTemplates.map(template => `
                        <div class="tbp-template-card" data-id="${template.id}">
                            <div class="tbp-template-preview">
                                ${template.thumbnail
                                    ? `<img src="${template.thumbnail}" alt="${template.title}">`
                                    : `<div class="tbp-template-placeholder"><span class="dashicons dashicons-format-image"></span></div>`
                                }
                                <div class="tbp-template-overlay">
                                    <button class="tbp-btn tbp-btn-primary tbp-edit-template" data-id="${template.id}">Edit</button>
                                    <button class="tbp-btn tbp-btn-secondary tbp-preview-template" data-id="${template.id}">Preview</button>
                                </div>
                            </div>
                            <div class="tbp-template-info">
                                <h4>${template.title}</h4>
                                <div class="tbp-template-meta">
                                    <span class="tbp-template-type">${template.type}</span>
                                    <span class="tbp-template-date">${template.date}</span>
                                </div>
                            </div>
                            <div class="tbp-template-actions">
                                <button class="tbp-btn-icon tbp-duplicate-template" data-id="${template.id}" title="Duplicate">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                                <button class="tbp-btn-icon tbp-delete-template" data-id="${template.id}" title="Delete">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>

                <!-- New Template Modal -->
                <div class="tbp-modal" id="tbp-new-template-modal">
                    <div class="tbp-modal-content">
                        <div class="tbp-modal-header">
                            <h3>Create New Template</h3>
                            <button class="tbp-modal-close">&times;</button>
                        </div>
                        <div class="tbp-modal-body">
                            <div class="tbp-form-group">
                                <label>Template Name</label>
                                <input type="text" id="tbp-template-name" placeholder="My Template">
                            </div>
                            <div class="tbp-form-group">
                                <label>Template Type</label>
                                <select id="tbp-template-type">
                                    <option value="page">Page</option>
                                    <option value="header">Header</option>
                                    <option value="footer">Footer</option>
                                    <option value="single">Single Post</option>
                                    <option value="archive">Archive</option>
                                    <option value="search">Search Results</option>
                                    <option value="404">404 Page</option>
                                    <option value="popup">Popup</option>
                                </select>
                            </div>
                            <div class="tbp-form-group">
                                <label>Start From</label>
                                <div class="tbp-template-starters">
                                    <label class="tbp-starter-option active">
                                        <input type="radio" name="starter" value="blank" checked>
                                        <div class="tbp-starter-card">
                                            <span class="dashicons dashicons-plus"></span>
                                            <span>Blank</span>
                                        </div>
                                    </label>
                                    <label class="tbp-starter-option">
                                        <input type="radio" name="starter" value="library">
                                        <div class="tbp-starter-card">
                                            <span class="dashicons dashicons-category"></span>
                                            <span>Library</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="tbp-modal-footer">
                            <button class="tbp-btn tbp-btn-secondary tbp-modal-cancel">Cancel</button>
                            <button class="tbp-btn tbp-btn-primary" id="tbp-create-template">Create Template</button>
                        </div>
                    </div>
                </div>
            `;

            this.bindEvents();
        },

        bindEvents() {
            // Filter buttons
            this.container.querySelectorAll('.tbp-filter-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    this.currentFilter = e.target.dataset.filter;
                    this.render();
                });
            });

            // New template button
            const newBtn = this.container.querySelector('#tbp-new-template');
            const createFirstBtn = this.container.querySelector('#tbp-create-first');
            const modal = this.container.querySelector('#tbp-new-template-modal');

            [newBtn, createFirstBtn].forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', () => {
                        modal.classList.add('active');
                    });
                }
            });

            // Modal close
            this.container.querySelectorAll('.tbp-modal-close, .tbp-modal-cancel').forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.classList.remove('active');
                });
            });

            // Create template
            const createBtn = this.container.querySelector('#tbp-create-template');
            if (createBtn) {
                createBtn.addEventListener('click', async () => {
                    const name = this.container.querySelector('#tbp-template-name').value || 'Untitled';
                    const type = this.container.querySelector('#tbp-template-type').value;

                    createBtn.disabled = true;
                    createBtn.textContent = 'Creating...';

                    const result = await API.request('tbp_create_template', { name, type });

                    if (result.success) {
                        window.location.href = result.data.edit_url;
                    } else {
                        alert('Error creating template');
                        createBtn.disabled = false;
                        createBtn.textContent = 'Create Template';
                    }
                });
            }

            // Edit template
            this.container.querySelectorAll('.tbp-edit-template').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    window.location.href = `${tbpAdmin.editorUrl}&post=${id}`;
                });
            });

            // Delete template
            this.container.querySelectorAll('.tbp-delete-template').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Are you sure you want to delete this template?')) return;

                    const id = btn.dataset.id;
                    const result = await API.request('tbp_delete_template', { id });

                    if (result.success) {
                        this.loadTemplates();
                    }
                });
            });

            // Duplicate template
            this.container.querySelectorAll('.tbp-duplicate-template').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const result = await API.request('tbp_duplicate_template', { id });

                    if (result.success) {
                        this.loadTemplates();
                    }
                });
            });

            // Starter options
            this.container.querySelectorAll('.tbp-starter-option').forEach(option => {
                option.addEventListener('click', () => {
                    this.container.querySelectorAll('.tbp-starter-option').forEach(o => o.classList.remove('active'));
                    option.classList.add('active');
                });
            });
        }
    };

    // ============================================
    // POPUPS PAGE
    // ============================================

    const PopupsPage = {
        container: null,
        popups: [],

        init() {
            this.container = document.getElementById('tbp-popups-app');
            if (!this.container) return;

            this.render();
            this.loadPopups();
        },

        async loadPopups() {
            const result = await API.request('tbp_get_popups');
            if (result.success) {
                this.popups = result.data;
                this.render();
            }
        },

        render() {
            this.container.innerHTML = `
                <div class="tbp-popups-header">
                    <div class="tbp-popups-stats">
                        <div class="tbp-stat-card">
                            <span class="tbp-stat-value">${this.popups.length}</span>
                            <span class="tbp-stat-label">Total Popups</span>
                        </div>
                        <div class="tbp-stat-card">
                            <span class="tbp-stat-value">${this.popups.filter(p => p.status === 'publish').length}</span>
                            <span class="tbp-stat-label">Active</span>
                        </div>
                        <div class="tbp-stat-card">
                            <span class="tbp-stat-value">${this.popups.reduce((a, p) => a + (p.views || 0), 0)}</span>
                            <span class="tbp-stat-label">Total Views</span>
                        </div>
                        <div class="tbp-stat-card">
                            <span class="tbp-stat-value">${this.popups.reduce((a, p) => a + (p.conversions || 0), 0)}</span>
                            <span class="tbp-stat-label">Conversions</span>
                        </div>
                    </div>
                    <button class="tbp-btn tbp-btn-primary" id="tbp-new-popup">
                        <span class="dashicons dashicons-plus-alt2"></span> New Popup
                    </button>
                </div>

                <div class="tbp-popups-list">
                    ${this.popups.length === 0 ? `
                        <div class="tbp-empty-state">
                            <div class="tbp-empty-icon">🎯</div>
                            <h3>No popups yet</h3>
                            <p>Create engaging popups to capture leads and boost conversions</p>
                            <button class="tbp-btn tbp-btn-primary" id="tbp-create-first-popup">Create Your First Popup</button>
                        </div>
                    ` : `
                        <table class="tbp-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Trigger</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Conversions</th>
                                    <th>Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${this.popups.map(popup => `
                                    <tr>
                                        <td><strong>${popup.title}</strong></td>
                                        <td><span class="tbp-badge">${popup.trigger || 'On Load'}</span></td>
                                        <td>
                                            <label class="tbp-switch">
                                                <input type="checkbox" ${popup.status === 'publish' ? 'checked' : ''} data-id="${popup.id}">
                                                <span class="tbp-switch-slider"></span>
                                            </label>
                                        </td>
                                        <td>${popup.views || 0}</td>
                                        <td>${popup.conversions || 0}</td>
                                        <td>${popup.views > 0 ? ((popup.conversions / popup.views) * 100).toFixed(1) : 0}%</td>
                                        <td>
                                            <button class="tbp-btn-icon tbp-edit-popup" data-id="${popup.id}" title="Edit">
                                                <span class="dashicons dashicons-edit"></span>
                                            </button>
                                            <button class="tbp-btn-icon tbp-duplicate-popup" data-id="${popup.id}" title="Duplicate">
                                                <span class="dashicons dashicons-admin-page"></span>
                                            </button>
                                            <button class="tbp-btn-icon tbp-delete-popup" data-id="${popup.id}" title="Delete">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `}
                </div>

                <!-- New Popup Modal -->
                <div class="tbp-modal" id="tbp-new-popup-modal">
                    <div class="tbp-modal-content tbp-modal-lg">
                        <div class="tbp-modal-header">
                            <h3>Create New Popup</h3>
                            <button class="tbp-modal-close">&times;</button>
                        </div>
                        <div class="tbp-modal-body">
                            <div class="tbp-form-group">
                                <label>Popup Name</label>
                                <input type="text" id="tbp-popup-name" placeholder="My Popup">
                            </div>

                            <div class="tbp-form-group">
                                <label>Choose a Template</label>
                                <div class="tbp-popup-templates">
                                    <label class="tbp-popup-template active">
                                        <input type="radio" name="popup-template" value="blank" checked>
                                        <div class="tbp-popup-template-card">
                                            <div class="tbp-popup-template-preview tbp-blank">
                                                <span class="dashicons dashicons-plus-alt2"></span>
                                            </div>
                                            <span>Blank</span>
                                        </div>
                                    </label>
                                    <label class="tbp-popup-template">
                                        <input type="radio" name="popup-template" value="newsletter">
                                        <div class="tbp-popup-template-card">
                                            <div class="tbp-popup-template-preview tbp-newsletter">
                                                <span class="dashicons dashicons-email-alt"></span>
                                            </div>
                                            <span>Newsletter</span>
                                        </div>
                                    </label>
                                    <label class="tbp-popup-template">
                                        <input type="radio" name="popup-template" value="discount">
                                        <div class="tbp-popup-template-card">
                                            <div class="tbp-popup-template-preview tbp-discount">
                                                <span class="dashicons dashicons-tag"></span>
                                            </div>
                                            <span>Discount</span>
                                        </div>
                                    </label>
                                    <label class="tbp-popup-template">
                                        <input type="radio" name="popup-template" value="announcement">
                                        <div class="tbp-popup-template-card">
                                            <div class="tbp-popup-template-preview tbp-announcement">
                                                <span class="dashicons dashicons-megaphone"></span>
                                            </div>
                                            <span>Announcement</span>
                                        </div>
                                    </label>
                                    <label class="tbp-popup-template">
                                        <input type="radio" name="popup-template" value="contact">
                                        <div class="tbp-popup-template-card">
                                            <div class="tbp-popup-template-preview tbp-contact">
                                                <span class="dashicons dashicons-phone"></span>
                                            </div>
                                            <span>Contact</span>
                                        </div>
                                    </label>
                                    <label class="tbp-popup-template">
                                        <input type="radio" name="popup-template" value="exit-intent">
                                        <div class="tbp-popup-template-card">
                                            <div class="tbp-popup-template-preview tbp-exit">
                                                <span class="dashicons dashicons-migrate"></span>
                                            </div>
                                            <span>Exit Intent</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="tbp-form-row">
                                <div class="tbp-form-group">
                                    <label>Trigger</label>
                                    <select id="tbp-popup-trigger">
                                        <option value="on_load">On Page Load</option>
                                        <option value="on_scroll">On Scroll</option>
                                        <option value="on_click">On Click</option>
                                        <option value="exit_intent">Exit Intent</option>
                                        <option value="after_time">After Time Delay</option>
                                        <option value="after_inactivity">After Inactivity</option>
                                    </select>
                                </div>
                                <div class="tbp-form-group">
                                    <label>Position</label>
                                    <select id="tbp-popup-position">
                                        <option value="center">Center</option>
                                        <option value="top">Top</option>
                                        <option value="bottom">Bottom</option>
                                        <option value="top-left">Top Left</option>
                                        <option value="top-right">Top Right</option>
                                        <option value="bottom-left">Bottom Left</option>
                                        <option value="bottom-right">Bottom Right</option>
                                        <option value="fullscreen">Fullscreen</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tbp-modal-footer">
                            <button class="tbp-btn tbp-btn-secondary tbp-modal-cancel">Cancel</button>
                            <button class="tbp-btn tbp-btn-primary" id="tbp-create-popup">Create Popup</button>
                        </div>
                    </div>
                </div>
            `;

            this.bindEvents();
        },

        bindEvents() {
            const modal = this.container.querySelector('#tbp-new-popup-modal');
            const newBtn = this.container.querySelector('#tbp-new-popup');
            const createFirstBtn = this.container.querySelector('#tbp-create-first-popup');

            [newBtn, createFirstBtn].forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', () => modal.classList.add('active'));
                }
            });

            this.container.querySelectorAll('.tbp-modal-close, .tbp-modal-cancel').forEach(btn => {
                btn.addEventListener('click', () => modal.classList.remove('active'));
            });

            // Template selection
            this.container.querySelectorAll('.tbp-popup-template').forEach(tpl => {
                tpl.addEventListener('click', () => {
                    this.container.querySelectorAll('.tbp-popup-template').forEach(t => t.classList.remove('active'));
                    tpl.classList.add('active');
                });
            });

            // Create popup
            const createBtn = this.container.querySelector('#tbp-create-popup');
            if (createBtn) {
                createBtn.addEventListener('click', async () => {
                    const name = this.container.querySelector('#tbp-popup-name').value || 'Untitled Popup';
                    const template = this.container.querySelector('input[name="popup-template"]:checked').value;
                    const trigger = this.container.querySelector('#tbp-popup-trigger').value;
                    const position = this.container.querySelector('#tbp-popup-position').value;

                    createBtn.disabled = true;
                    createBtn.textContent = 'Creating...';

                    const result = await API.request('tbp_create_popup', { name, template, trigger, position });

                    if (result.success) {
                        window.location.href = result.data.edit_url;
                    } else {
                        alert('Error creating popup');
                        createBtn.disabled = false;
                        createBtn.textContent = 'Create Popup';
                    }
                });
            }

            // Toggle status
            this.container.querySelectorAll('.tbp-switch input').forEach(toggle => {
                toggle.addEventListener('change', async (e) => {
                    const id = e.target.dataset.id;
                    const status = e.target.checked ? 'publish' : 'draft';
                    await API.request('tbp_update_popup_status', { id, status });
                });
            });

            // Delete popup
            this.container.querySelectorAll('.tbp-delete-popup').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Delete this popup?')) return;
                    const id = btn.dataset.id;
                    await API.request('tbp_delete_popup', { id });
                    this.loadPopups();
                });
            });

            // Edit popup
            this.container.querySelectorAll('.tbp-edit-popup').forEach(btn => {
                btn.addEventListener('click', () => {
                    window.location.href = `${tbpAdmin.editorUrl}&post=${btn.dataset.id}`;
                });
            });
        }
    };

    // ============================================
    // GLOBAL STYLES PAGE
    // ============================================

    const GlobalStylesPage = {
        container: null,
        styles: {
            colors: {
                primary: '#6366f1',
                secondary: '#64748b',
                accent: '#f59e0b',
                text: '#1e293b',
                textLight: '#64748b',
                background: '#ffffff',
                backgroundAlt: '#f8fafc'
            },
            typography: {
                primaryFont: 'Inter',
                secondaryFont: 'Inter',
                baseSize: 16,
                scaleRatio: 1.25
            },
            spacing: {
                unit: 8,
                containerWidth: 1200
            },
            buttons: {
                borderRadius: 8,
                padding: '12px 24px'
            }
        },

        init() {
            this.container = document.getElementById('tbp-global-styles-app');
            if (!this.container) return;

            this.loadStyles();
        },

        async loadStyles() {
            const result = await API.request('tbp_get_global_styles');
            if (result.success && result.data) {
                this.styles = { ...this.styles, ...result.data };
            }
            this.render();
        },

        render() {
            this.container.innerHTML = `
                <div class="tbp-styles-layout">
                    <div class="tbp-styles-sidebar">
                        <nav class="tbp-styles-nav">
                            <button class="tbp-styles-nav-item active" data-section="colors">
                                <span class="dashicons dashicons-art"></span> Colors
                            </button>
                            <button class="tbp-styles-nav-item" data-section="typography">
                                <span class="dashicons dashicons-editor-textcolor"></span> Typography
                            </button>
                            <button class="tbp-styles-nav-item" data-section="spacing">
                                <span class="dashicons dashicons-image-crop"></span> Spacing
                            </button>
                            <button class="tbp-styles-nav-item" data-section="buttons">
                                <span class="dashicons dashicons-button"></span> Buttons
                            </button>
                        </nav>
                    </div>

                    <div class="tbp-styles-content">
                        <!-- Colors Section -->
                        <div class="tbp-styles-section active" id="section-colors">
                            <h3>Global Colors</h3>
                            <p class="tbp-section-desc">Define your brand colors used throughout the site</p>

                            <div class="tbp-colors-grid">
                                ${Object.entries(this.styles.colors).map(([key, value]) => `
                                    <div class="tbp-color-item">
                                        <label>${this.formatLabel(key)}</label>
                                        <div class="tbp-color-picker-wrap">
                                            <input type="color" value="${value}" data-color="${key}" class="tbp-color-input">
                                            <input type="text" value="${value}" data-color="${key}" class="tbp-color-text">
                                        </div>
                                    </div>
                                `).join('')}
                            </div>

                            <button class="tbp-btn tbp-btn-secondary" id="tbp-add-color">
                                <span class="dashicons dashicons-plus"></span> Add Custom Color
                            </button>
                        </div>

                        <!-- Typography Section -->
                        <div class="tbp-styles-section" id="section-typography">
                            <h3>Typography</h3>
                            <p class="tbp-section-desc">Configure fonts and text styles</p>

                            <div class="tbp-form-row">
                                <div class="tbp-form-group">
                                    <label>Primary Font</label>
                                    <select class="tbp-font-select" data-font="primaryFont">
                                        ${this.getFontOptions(this.styles.typography.primaryFont)}
                                    </select>
                                </div>
                                <div class="tbp-form-group">
                                    <label>Secondary Font</label>
                                    <select class="tbp-font-select" data-font="secondaryFont">
                                        ${this.getFontOptions(this.styles.typography.secondaryFont)}
                                    </select>
                                </div>
                            </div>

                            <div class="tbp-form-row">
                                <div class="tbp-form-group">
                                    <label>Base Font Size</label>
                                    <div class="tbp-input-with-unit">
                                        <input type="number" value="${this.styles.typography.baseSize}" data-typo="baseSize" min="12" max="24">
                                        <span>px</span>
                                    </div>
                                </div>
                                <div class="tbp-form-group">
                                    <label>Scale Ratio</label>
                                    <select data-typo="scaleRatio">
                                        <option value="1.125" ${this.styles.typography.scaleRatio == 1.125 ? 'selected' : ''}>Minor Second (1.125)</option>
                                        <option value="1.2" ${this.styles.typography.scaleRatio == 1.2 ? 'selected' : ''}>Minor Third (1.2)</option>
                                        <option value="1.25" ${this.styles.typography.scaleRatio == 1.25 ? 'selected' : ''}>Major Third (1.25)</option>
                                        <option value="1.333" ${this.styles.typography.scaleRatio == 1.333 ? 'selected' : ''}>Perfect Fourth (1.333)</option>
                                        <option value="1.5" ${this.styles.typography.scaleRatio == 1.5 ? 'selected' : ''}>Perfect Fifth (1.5)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="tbp-typography-preview">
                                <h4>Preview</h4>
                                <div class="tbp-typo-scale" style="font-family: ${this.styles.typography.primaryFont}">
                                    <div style="font-size: ${this.styles.typography.baseSize * Math.pow(this.styles.typography.scaleRatio, 4)}px">Heading 1</div>
                                    <div style="font-size: ${this.styles.typography.baseSize * Math.pow(this.styles.typography.scaleRatio, 3)}px">Heading 2</div>
                                    <div style="font-size: ${this.styles.typography.baseSize * Math.pow(this.styles.typography.scaleRatio, 2)}px">Heading 3</div>
                                    <div style="font-size: ${this.styles.typography.baseSize * this.styles.typography.scaleRatio}px">Heading 4</div>
                                    <div style="font-size: ${this.styles.typography.baseSize}px">Body text - The quick brown fox jumps over the lazy dog.</div>
                                    <div style="font-size: ${this.styles.typography.baseSize * 0.875}px">Small text</div>
                                </div>
                            </div>
                        </div>

                        <!-- Spacing Section -->
                        <div class="tbp-styles-section" id="section-spacing">
                            <h3>Spacing & Layout</h3>
                            <p class="tbp-section-desc">Configure spacing units and container widths</p>

                            <div class="tbp-form-row">
                                <div class="tbp-form-group">
                                    <label>Spacing Unit</label>
                                    <div class="tbp-input-with-unit">
                                        <input type="number" value="${this.styles.spacing.unit}" data-spacing="unit" min="4" max="16">
                                        <span>px</span>
                                    </div>
                                    <p class="tbp-help-text">Base unit for margins and padding (multiplied for different sizes)</p>
                                </div>
                                <div class="tbp-form-group">
                                    <label>Container Width</label>
                                    <div class="tbp-input-with-unit">
                                        <input type="number" value="${this.styles.spacing.containerWidth}" data-spacing="containerWidth" min="960" max="1920">
                                        <span>px</span>
                                    </div>
                                </div>
                            </div>

                            <div class="tbp-spacing-preview">
                                <h4>Spacing Scale</h4>
                                <div class="tbp-spacing-scale">
                                    ${[1, 2, 3, 4, 6, 8, 12, 16].map(mult => `
                                        <div class="tbp-spacing-item">
                                            <div class="tbp-spacing-box" style="width: ${this.styles.spacing.unit * mult}px; height: ${this.styles.spacing.unit * mult}px"></div>
                                            <span>${mult}x = ${this.styles.spacing.unit * mult}px</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>

                        <!-- Buttons Section -->
                        <div class="tbp-styles-section" id="section-buttons">
                            <h3>Button Styles</h3>
                            <p class="tbp-section-desc">Configure default button appearance</p>

                            <div class="tbp-form-row">
                                <div class="tbp-form-group">
                                    <label>Border Radius</label>
                                    <input type="range" min="0" max="50" value="${this.styles.buttons.borderRadius}" data-button="borderRadius" class="tbp-range">
                                    <span class="tbp-range-value">${this.styles.buttons.borderRadius}px</span>
                                </div>
                            </div>

                            <div class="tbp-buttons-preview">
                                <h4>Preview</h4>
                                <div class="tbp-button-samples" style="--btn-radius: ${this.styles.buttons.borderRadius}px; --primary: ${this.styles.colors.primary}; --secondary: ${this.styles.colors.secondary}">
                                    <button class="tbp-sample-btn primary">Primary Button</button>
                                    <button class="tbp-sample-btn secondary">Secondary Button</button>
                                    <button class="tbp-sample-btn outline">Outline Button</button>
                                    <button class="tbp-sample-btn text">Text Button</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tbp-styles-preview-panel">
                        <h4>Live Preview</h4>
                        <div class="tbp-live-preview" style="
                            --color-primary: ${this.styles.colors.primary};
                            --color-secondary: ${this.styles.colors.secondary};
                            --color-accent: ${this.styles.colors.accent};
                            --color-text: ${this.styles.colors.text};
                            --color-bg: ${this.styles.colors.background};
                            --font-primary: ${this.styles.typography.primaryFont};
                            --font-size: ${this.styles.typography.baseSize}px;
                        ">
                            <div class="preview-header">
                                <div class="preview-logo">Logo</div>
                                <nav class="preview-nav">
                                    <a href="#">Home</a>
                                    <a href="#">About</a>
                                    <a href="#">Services</a>
                                    <a href="#">Contact</a>
                                </nav>
                            </div>
                            <div class="preview-hero">
                                <h1>Welcome to Your Site</h1>
                                <p>This is how your content will look with the current styles.</p>
                                <button>Get Started</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tbp-styles-footer">
                    <button class="tbp-btn tbp-btn-secondary" id="tbp-reset-styles">Reset to Defaults</button>
                    <button class="tbp-btn tbp-btn-primary" id="tbp-save-styles">
                        <span class="dashicons dashicons-saved"></span> Save Changes
                    </button>
                </div>
            `;

            this.bindEvents();
        },

        formatLabel(key) {
            return key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
        },

        getFontOptions(selected) {
            const fonts = [
                'Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins',
                'Source Sans Pro', 'Raleway', 'Nunito', 'Work Sans', 'Playfair Display',
                'Merriweather', 'PT Sans', 'Ubuntu', 'Oswald', 'Rubik'
            ];
            return fonts.map(font => `<option value="${font}" ${font === selected ? 'selected' : ''}>${font}</option>`).join('');
        },

        bindEvents() {
            // Section navigation
            this.container.querySelectorAll('.tbp-styles-nav-item').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.container.querySelectorAll('.tbp-styles-nav-item').forEach(b => b.classList.remove('active'));
                    this.container.querySelectorAll('.tbp-styles-section').forEach(s => s.classList.remove('active'));
                    btn.classList.add('active');
                    this.container.querySelector(`#section-${btn.dataset.section}`).classList.add('active');
                });
            });

            // Color inputs
            this.container.querySelectorAll('.tbp-color-input').forEach(input => {
                input.addEventListener('input', (e) => {
                    const key = e.target.dataset.color;
                    this.styles.colors[key] = e.target.value;
                    this.container.querySelector(`.tbp-color-text[data-color="${key}"]`).value = e.target.value;
                    this.updatePreview();
                });
            });

            this.container.querySelectorAll('.tbp-color-text').forEach(input => {
                input.addEventListener('input', (e) => {
                    const key = e.target.dataset.color;
                    if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
                        this.styles.colors[key] = e.target.value;
                        this.container.querySelector(`.tbp-color-input[data-color="${key}"]`).value = e.target.value;
                        this.updatePreview();
                    }
                });
            });

            // Typography
            this.container.querySelectorAll('.tbp-font-select').forEach(select => {
                select.addEventListener('change', (e) => {
                    this.styles.typography[e.target.dataset.font] = e.target.value;
                    this.updatePreview();
                });
            });

            this.container.querySelectorAll('[data-typo]').forEach(input => {
                input.addEventListener('input', (e) => {
                    this.styles.typography[e.target.dataset.typo] = parseFloat(e.target.value);
                    this.updatePreview();
                });
            });

            // Spacing
            this.container.querySelectorAll('[data-spacing]').forEach(input => {
                input.addEventListener('input', (e) => {
                    this.styles.spacing[e.target.dataset.spacing] = parseInt(e.target.value);
                    this.updatePreview();
                });
            });

            // Buttons
            this.container.querySelectorAll('[data-button]').forEach(input => {
                input.addEventListener('input', (e) => {
                    this.styles.buttons[e.target.dataset.button] = parseInt(e.target.value);
                    this.container.querySelector('.tbp-range-value').textContent = e.target.value + 'px';
                    this.updatePreview();
                });
            });

            // Save
            this.container.querySelector('#tbp-save-styles').addEventListener('click', async () => {
                const btn = this.container.querySelector('#tbp-save-styles');
                btn.disabled = true;
                btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Saving...';

                const result = await API.request('tbp_save_global_styles', { styles: this.styles });

                if (result.success) {
                    btn.innerHTML = '<span class="dashicons dashicons-yes"></span> Saved!';
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<span class="dashicons dashicons-saved"></span> Save Changes';
                    }, 2000);
                }
            });

            // Reset
            this.container.querySelector('#tbp-reset-styles').addEventListener('click', () => {
                if (confirm('Reset all styles to defaults?')) {
                    this.styles = {
                        colors: { primary: '#6366f1', secondary: '#64748b', accent: '#f59e0b', text: '#1e293b', textLight: '#64748b', background: '#ffffff', backgroundAlt: '#f8fafc' },
                        typography: { primaryFont: 'Inter', secondaryFont: 'Inter', baseSize: 16, scaleRatio: 1.25 },
                        spacing: { unit: 8, containerWidth: 1200 },
                        buttons: { borderRadius: 8 }
                    };
                    this.render();
                }
            });
        },

        updatePreview() {
            const preview = this.container.querySelector('.tbp-live-preview');
            if (preview) {
                preview.style.setProperty('--color-primary', this.styles.colors.primary);
                preview.style.setProperty('--color-secondary', this.styles.colors.secondary);
                preview.style.setProperty('--color-text', this.styles.colors.text);
                preview.style.setProperty('--color-bg', this.styles.colors.background);
                preview.style.setProperty('--font-primary', this.styles.typography.primaryFont);
                preview.style.setProperty('--font-size', this.styles.typography.baseSize + 'px');
            }
        }
    };

    // ============================================
    // FORMS PAGE
    // ============================================

    const FormsPage = {
        container: null,
        forms: [],
        submissions: [],

        init() {
            this.container = document.getElementById('tbp-forms-app');
            if (!this.container) return;

            this.loadData();
        },

        async loadData() {
            const [formsResult, submissionsResult] = await Promise.all([
                API.request('tbp_get_forms'),
                API.request('tbp_get_submissions')
            ]);

            if (formsResult.success) this.forms = formsResult.data || [];
            if (submissionsResult.success) this.submissions = submissionsResult.data || [];

            this.render();
        },

        render() {
            this.container.innerHTML = `
                <div class="tbp-forms-tabs">
                    <button class="tbp-tab active" data-tab="forms">Forms</button>
                    <button class="tbp-tab" data-tab="submissions">Submissions <span class="tbp-badge">${this.submissions.length}</span></button>
                </div>

                <div class="tbp-tab-content active" id="tab-forms">
                    ${this.forms.length === 0 ? `
                        <div class="tbp-empty-state">
                            <div class="tbp-empty-icon">📝</div>
                            <h3>No forms yet</h3>
                            <p>Create forms using the Form widget in the editor</p>
                            <a href="${tbpAdmin.editorUrl}" class="tbp-btn tbp-btn-primary">Open Editor</a>
                        </div>
                    ` : `
                        <div class="tbp-forms-list">
                            ${this.forms.map(form => `
                                <div class="tbp-form-card">
                                    <div class="tbp-form-info">
                                        <h4>${form.name}</h4>
                                        <span class="tbp-form-meta">${form.fields} fields • ${form.submissions} submissions</span>
                                    </div>
                                    <div class="tbp-form-actions">
                                        <button class="tbp-btn tbp-btn-secondary tbp-btn-sm">Edit</button>
                                        <button class="tbp-btn tbp-btn-secondary tbp-btn-sm">Export</button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `}
                </div>

                <div class="tbp-tab-content" id="tab-submissions">
                    ${this.submissions.length === 0 ? `
                        <div class="tbp-empty-state">
                            <div class="tbp-empty-icon">📬</div>
                            <h3>No submissions yet</h3>
                            <p>Form submissions will appear here</p>
                        </div>
                    ` : `
                        <div class="tbp-submissions-header">
                            <div class="tbp-submissions-actions">
                                <button class="tbp-btn tbp-btn-secondary tbp-btn-sm" id="tbp-export-csv">
                                    <span class="dashicons dashicons-download"></span> Export CSV
                                </button>
                                <button class="tbp-btn tbp-btn-secondary tbp-btn-sm" id="tbp-delete-selected">
                                    <span class="dashicons dashicons-trash"></span> Delete Selected
                                </button>
                            </div>
                        </div>
                        <table class="tbp-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="tbp-select-all"></th>
                                    <th>Form</th>
                                    <th>Data</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${this.submissions.map(sub => `
                                    <tr>
                                        <td><input type="checkbox" data-id="${sub.id}"></td>
                                        <td>${sub.form_name}</td>
                                        <td><code>${JSON.stringify(sub.data).substring(0, 50)}...</code></td>
                                        <td>${sub.date}</td>
                                        <td>
                                            <button class="tbp-btn-icon tbp-view-submission" data-id="${sub.id}">
                                                <span class="dashicons dashicons-visibility"></span>
                                            </button>
                                            <button class="tbp-btn-icon tbp-delete-submission" data-id="${sub.id}">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `}
                </div>
            `;

            this.bindEvents();
        },

        bindEvents() {
            // Tab switching
            this.container.querySelectorAll('.tbp-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    this.container.querySelectorAll('.tbp-tab').forEach(t => t.classList.remove('active'));
                    this.container.querySelectorAll('.tbp-tab-content').forEach(c => c.classList.remove('active'));
                    tab.classList.add('active');
                    this.container.querySelector(`#tab-${tab.dataset.tab}`).classList.add('active');
                });
            });

            // Select all
            const selectAll = this.container.querySelector('#tbp-select-all');
            if (selectAll) {
                selectAll.addEventListener('change', (e) => {
                    this.container.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => {
                        cb.checked = e.target.checked;
                    });
                });
            }

            // Export CSV
            const exportBtn = this.container.querySelector('#tbp-export-csv');
            if (exportBtn) {
                exportBtn.addEventListener('click', () => {
                    window.location.href = `${tbpAdmin.ajaxUrl}?action=tbp_export_submissions&nonce=${tbpAdmin.nonce}`;
                });
            }
        }
    };

    // ============================================
    // THEME BUILDER PAGE
    // ============================================

    const ThemeBuilderPage = {
        container: null,
        locations: [],

        init() {
            this.container = document.getElementById('tbp-theme-builder-app');
            if (!this.container) return;

            this.loadLocations();
        },

        async loadLocations() {
            const result = await API.request('tbp_get_theme_locations');
            if (result.success) {
                this.locations = result.data;
            }
            this.render();
        },

        render() {
            const locationGroups = {
                'Site Parts': ['header', 'footer'],
                'Single': ['single', 'page', 'post'],
                'Archive': ['archive', 'category', 'tag', 'author', 'search', '404'],
                'WooCommerce': ['product', 'product_archive', 'cart', 'checkout']
            };

            this.container.innerHTML = `
                <div class="tbp-theme-builder-header">
                    <h2>Theme Builder</h2>
                    <p>Create custom templates for different parts of your site</p>
                </div>

                <div class="tbp-theme-locations">
                    ${Object.entries(locationGroups).map(([group, locations]) => `
                        <div class="tbp-location-group">
                            <h3>${group}</h3>
                            <div class="tbp-locations-grid">
                                ${locations.map(loc => {
                                    const location = this.locations.find(l => l.id === loc) || { id: loc, name: this.formatName(loc), template: null };
                                    return `
                                        <div class="tbp-location-card ${location.template ? 'has-template' : ''}" data-location="${loc}">
                                            <div class="tbp-location-icon">
                                                ${this.getIcon(loc)}
                                            </div>
                                            <div class="tbp-location-info">
                                                <h4>${location.name}</h4>
                                                ${location.template
                                                    ? `<span class="tbp-location-template">${location.template.title}</span>`
                                                    : `<span class="tbp-location-empty">No template assigned</span>`
                                                }
                                            </div>
                                            <div class="tbp-location-actions">
                                                ${location.template
                                                    ? `
                                                        <button class="tbp-btn tbp-btn-sm tbp-btn-primary tbp-edit-location" data-id="${location.template.id}">Edit</button>
                                                        <button class="tbp-btn tbp-btn-sm tbp-btn-secondary tbp-change-location" data-location="${loc}">Change</button>
                                                    `
                                                    : `<button class="tbp-btn tbp-btn-sm tbp-btn-primary tbp-add-location" data-location="${loc}">Add Template</button>`
                                                }
                                            </div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>

                <!-- Assign Template Modal -->
                <div class="tbp-modal" id="tbp-assign-template-modal">
                    <div class="tbp-modal-content">
                        <div class="tbp-modal-header">
                            <h3>Assign Template</h3>
                            <button class="tbp-modal-close">&times;</button>
                        </div>
                        <div class="tbp-modal-body">
                            <div class="tbp-template-options">
                                <button class="tbp-template-option" data-action="create">
                                    <span class="dashicons dashicons-plus-alt2"></span>
                                    <span>Create New Template</span>
                                </button>
                                <button class="tbp-template-option" data-action="existing">
                                    <span class="dashicons dashicons-category"></span>
                                    <span>Choose Existing</span>
                                </button>
                            </div>
                            <div class="tbp-existing-templates" style="display: none;">
                                <select id="tbp-template-select">
                                    <option value="">Select a template...</option>
                                </select>
                            </div>
                        </div>
                        <div class="tbp-modal-footer">
                            <button class="tbp-btn tbp-btn-secondary tbp-modal-cancel">Cancel</button>
                            <button class="tbp-btn tbp-btn-primary" id="tbp-assign-template" disabled>Assign</button>
                        </div>
                    </div>
                </div>
            `;

            this.bindEvents();
        },

        formatName(loc) {
            return loc.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        getIcon(loc) {
            const icons = {
                header: '<span class="dashicons dashicons-arrow-up-alt"></span>',
                footer: '<span class="dashicons dashicons-arrow-down-alt"></span>',
                single: '<span class="dashicons dashicons-media-text"></span>',
                page: '<span class="dashicons dashicons-admin-page"></span>',
                post: '<span class="dashicons dashicons-admin-post"></span>',
                archive: '<span class="dashicons dashicons-list-view"></span>',
                category: '<span class="dashicons dashicons-category"></span>',
                tag: '<span class="dashicons dashicons-tag"></span>',
                author: '<span class="dashicons dashicons-admin-users"></span>',
                search: '<span class="dashicons dashicons-search"></span>',
                '404': '<span class="dashicons dashicons-warning"></span>',
                product: '<span class="dashicons dashicons-cart"></span>',
                product_archive: '<span class="dashicons dashicons-store"></span>',
                cart: '<span class="dashicons dashicons-cart"></span>',
                checkout: '<span class="dashicons dashicons-money-alt"></span>'
            };
            return icons[loc] || '<span class="dashicons dashicons-layout"></span>';
        },

        bindEvents() {
            const modal = this.container.querySelector('#tbp-assign-template-modal');
            let currentLocation = null;

            // Add template
            this.container.querySelectorAll('.tbp-add-location, .tbp-change-location').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentLocation = btn.dataset.location;
                    modal.classList.add('active');
                });
            });

            // Modal close
            this.container.querySelectorAll('.tbp-modal-close, .tbp-modal-cancel').forEach(btn => {
                btn.addEventListener('click', () => modal.classList.remove('active'));
            });

            // Template options
            this.container.querySelectorAll('.tbp-template-option').forEach(opt => {
                opt.addEventListener('click', async () => {
                    if (opt.dataset.action === 'create') {
                        const result = await API.request('tbp_create_template', {
                            name: `${this.formatName(currentLocation)} Template`,
                            type: currentLocation
                        });
                        if (result.success) {
                            window.location.href = result.data.edit_url;
                        }
                    } else {
                        this.container.querySelector('.tbp-existing-templates').style.display = 'block';
                    }
                });
            });

            // Edit location
            this.container.querySelectorAll('.tbp-edit-location').forEach(btn => {
                btn.addEventListener('click', () => {
                    window.location.href = `${tbpAdmin.editorUrl}&post=${btn.dataset.id}`;
                });
            });
        }
    };

    // ============================================
    // INITIALIZATION
    // ============================================

    document.addEventListener('DOMContentLoaded', () => {
        TemplatesPage.init();
        PopupsPage.init();
        GlobalStylesPage.init();
        FormsPage.init();
        ThemeBuilderPage.init();
    });

})(jQuery);
