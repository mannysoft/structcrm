document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();

    // =============================================
    // DATA STORE — localStorage persistence
    // =============================================
    class DataStore {
        constructor() {
            this.STORAGE_KEY = 'structcrm_data';
            if (!localStorage.getItem(this.STORAGE_KEY)) {
                this.seed();
            }
        }

        _getData() {
            return JSON.parse(localStorage.getItem(this.STORAGE_KEY));
        }

        _setData(data) {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
        }

        getAll(entity) {
            return this._getData()[entity] || [];
        }

        getById(entity, id) {
            return this.getAll(entity).find(item => item.id === id);
        }

        create(entity, item) {
            const data = this._getData();
            item.id = Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
            item.createdAt = new Date().toISOString();
            data[entity].push(item);
            this._setData(data);
            return item;
        }

        update(entity, id, updates) {
            const data = this._getData();
            const idx = data[entity].findIndex(item => item.id === id);
            if (idx === -1) return null;
            data[entity][idx] = { ...data[entity][idx], ...updates };
            this._setData(data);
            return data[entity][idx];
        }

        delete(entity, id) {
            const data = this._getData();
            data[entity] = data[entity].filter(item => item.id !== id);
            this._setData(data);
        }

        exportAll() {
            return this._getData();
        }

        importAll(jsonData) {
            this._setData(jsonData);
        }

        clearAll() {
            localStorage.removeItem(this.STORAGE_KEY);
            this.seed();
        }

        seed() {
            const data = {
                leads: [
                    { id: 'l1', title: 'Garage Addition', client: 'Johnson Family', value: '$45k', status: 'New', createdAt: '2026-10-18T10:00:00Z' },
                    { id: 'l2', title: 'Office Buildout', client: 'TechCorp Inc.', value: '$120k', status: 'New', createdAt: '2026-10-17T08:00:00Z' },
                    { id: 'l3', title: 'Deck Extension', client: 'Sarah Miller', value: '$15k', status: 'New', createdAt: '2026-10-19T14:00:00Z' },
                    { id: 'l4', title: 'Kitchen Remodel', client: 'David Chen', value: '$65k', status: 'Contacted', createdAt: '2026-10-14T09:00:00Z' },
                    { id: 'l5', title: 'Roof Replacement', client: 'Local Bakery', value: '$22k', status: 'Contacted', createdAt: '2026-10-12T11:00:00Z' },
                    { id: 'l6', title: 'Basement Finish', client: 'The Wilsons', value: '$55k', status: 'Estimate Sent', createdAt: '2026-10-10T15:00:00Z' },
                    { id: 'l7', title: 'Custom Home', client: 'Elena R.', value: '$850k', status: 'Estimate Sent', createdAt: '2026-10-08T12:00:00Z' },
                    { id: 'l8', title: 'Retail Storefront', client: 'Boutique LLC', value: '$95k', status: 'Won', createdAt: '2026-10-05T16:00:00Z' }
                ],
                projects: [
                    { id: 'p1', name: 'Downtown Office Buildout', client: 'TechCorp Inc.', status: 'Active', progress: 45, startDate: '2026-10-01', endDate: '2026-12-15', createdAt: '2026-10-01T00:00:00Z' },
                    { id: 'p2', name: 'Smith Residence Renovation', client: 'John Smith', status: 'Pending', progress: 0, startDate: '2026-11-01', endDate: '2027-01-30', createdAt: '2026-10-15T00:00:00Z' },
                    { id: 'p3', name: 'Westside Retail Plaza', client: 'Retail Partners', status: 'Active', progress: 85, startDate: '2026-08-01', endDate: '2026-11-10', createdAt: '2026-08-01T00:00:00Z' },
                    { id: 'p4', name: 'Community Center Roof', client: 'City Council', status: 'Completed', progress: 100, startDate: '2026-07-15', endDate: '2026-09-30', createdAt: '2026-07-15T00:00:00Z' }
                ],
                tasks: [
                    { id: 't1', title: 'Order framing materials', project: 'Smith Residence', due: '2026-10-25', priority: 'high', completed: false, createdAt: '2026-10-20T00:00:00Z' },
                    { id: 't2', title: 'Schedule electrical inspection', project: 'Downtown Office', due: '2026-10-26', priority: 'med', completed: false, createdAt: '2026-10-19T00:00:00Z' },
                    { id: 't3', title: 'Client walkthrough', project: 'Westside Retail', due: '2026-10-28', priority: 'med', completed: false, createdAt: '2026-10-18T00:00:00Z' },
                    { id: 't4', title: 'Submit permits', project: 'Community Center', due: '2026-11-02', priority: 'low', completed: false, createdAt: '2026-10-17T00:00:00Z' }
                ],
                changeOrders: [
                    { id: 'co1', coNumber: 'CO-104', project: 'Smith Residence', desc: 'Upgrade kitchen countertops to Quartz', amount: '+$3,200', status: 'Pending Approval', createdAt: '2026-10-22T00:00:00Z' },
                    { id: 'co2', coNumber: 'CO-103', project: 'Downtown Office', desc: 'Add 4 extra floor outlets in conference room', amount: '+$850', status: 'Approved', createdAt: '2026-10-20T00:00:00Z' },
                    { id: 'co3', coNumber: 'CO-102', project: 'Westside Retail', desc: 'Remove interior partition wall', amount: '-$1,500', status: 'Approved', createdAt: '2026-10-18T00:00:00Z' }
                ],
                invoices: [
                    { id: 'i1', invoiceNumber: 'INV-2026-042', project: 'Downtown Office', amount: 25000, date: '2026-10-15', status: 'Paid', createdAt: '2026-10-15T00:00:00Z' },
                    { id: 'i2', invoiceNumber: 'INV-2026-043', project: 'Smith Residence', amount: 12500, date: '2026-10-20', status: 'Pending', createdAt: '2026-10-20T00:00:00Z' },
                    { id: 'i3', invoiceNumber: 'INV-2026-044', project: 'Westside Retail', amount: 45000, date: '2026-09-30', status: 'Overdue', createdAt: '2026-09-30T00:00:00Z' },
                    { id: 'i4', invoiceNumber: 'INV-2026-038', project: 'Downtown Office', amount: 18000, date: '2026-09-15', status: 'Paid', createdAt: '2026-09-15T00:00:00Z' },
                    { id: 'i5', invoiceNumber: 'INV-2026-035', project: 'Westside Retail', amount: 30000, date: '2026-08-20', status: 'Paid', createdAt: '2026-08-20T00:00:00Z' },
                    { id: 'i6', invoiceNumber: 'INV-2026-030', project: 'Community Center', amount: 22000, date: '2026-08-01', status: 'Paid', createdAt: '2026-08-01T00:00:00Z' }
                ],
                notifications: []
            };
            this._setData(data);
        }
    }

    const store = new DataStore();

    // =============================================
    // APP STATE & DOM REFERENCES
    // =============================================
    const state = { currentView: 'dashboard' };

    const el = {
        navItems: document.querySelectorAll('.nav-item'),
        contentArea: document.getElementById('content-area'),
        viewTitle: document.getElementById('current-view-title'),
        mobileBtn: document.getElementById('mobile-menu-btn'),
        sidebar: document.getElementById('sidebar'),
        newItemBtn: document.getElementById('new-item-btn'),
        globalModal: document.getElementById('global-modal'),
        closeModalBtn: document.getElementById('close-modal-btn'),
        modalTitle: document.getElementById('modal-title'),
        modalBody: document.getElementById('modal-body'),
        themeToggleBtn: document.getElementById('theme-toggle-btn'),
        searchInput: document.getElementById('global-search-input'),
        searchResults: document.getElementById('search-results'),
        notificationBtn: document.getElementById('notification-btn'),
        notificationPanel: document.getElementById('notification-panel'),
        notificationBadge: document.getElementById('notification-badge'),
        notificationList: document.getElementById('notification-list'),
        markAllReadBtn: document.getElementById('mark-all-read-btn')
    };

    // =============================================
    // THEME (Dark Mode)
    // =============================================
    function initTheme() {
        const saved = localStorage.getItem('structcrm_theme');
        if (saved) {
            document.documentElement.setAttribute('data-theme', saved);
        } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
        updateThemeButton();
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('structcrm_theme', next);
        updateThemeButton();
    }

    function updateThemeButton() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const btn = el.themeToggleBtn;
        if (btn) {
            btn.innerHTML = isDark
                ? '<i data-lucide="sun"></i><span>Light Mode</span>'
                : '<i data-lucide="moon"></i><span>Dark Mode</span>';
            lucide.createIcons({ nodes: [btn] });
        }
    }

    initTheme();
    el.themeToggleBtn.addEventListener('click', toggleTheme);

    // =============================================
    // MODAL
    // =============================================
    function openModal(title, contentHTML) {
        el.modalTitle.textContent = title;
        el.modalBody.innerHTML = contentHTML;
        el.globalModal.classList.add('open');
        lucide.createIcons({ nodes: [el.modalBody] });
    }

    function closeModal() {
        el.globalModal.classList.remove('open');
    }

    el.newItemBtn.addEventListener('click', () => showCreateOptions());
    el.closeModalBtn.addEventListener('click', closeModal);
    el.globalModal.addEventListener('click', (e) => {
        if (e.target === el.globalModal) closeModal();
    });

    function showCreateOptions() {
        openModal('Create New', `
            <div class="create-options-grid">
                <div class="create-option" id="create-lead-option">
                    <div class="option-icon" style="background:var(--accent-soft);color:var(--accent);"><i data-lucide="user-plus"></i></div>
                    <div><h4>New Lead</h4><p>Add a potential client</p></div>
                </div>
                <div class="create-option" id="create-project-option">
                    <div class="option-icon" style="background:var(--success-soft);color:var(--success);"><i data-lucide="briefcase"></i></div>
                    <div><h4>New Project</h4><p>Start a new job</p></div>
                </div>
                <div class="create-option" id="create-task-option">
                    <div class="option-icon" style="background:var(--warning-soft);color:var(--warning);"><i data-lucide="check-square"></i></div>
                    <div><h4>New Task</h4><p>Assign work to team</p></div>
                </div>
                <div class="create-option" id="create-co-option">
                    <div class="option-icon" style="background:var(--danger-soft);color:var(--danger);"><i data-lucide="file-diff"></i></div>
                    <div><h4>New Change Order</h4><p>Modify project scope</p></div>
                </div>
                <div class="create-option" id="create-invoice-option">
                    <div class="option-icon" style="background:var(--purple-soft);color:var(--purple);"><i data-lucide="receipt"></i></div>
                    <div><h4>New Invoice</h4><p>Bill a client</p></div>
                </div>
            </div>
        `);

        document.getElementById('create-lead-option').addEventListener('click', () => showLeadForm());
        document.getElementById('create-project-option').addEventListener('click', () => showProjectForm());
        document.getElementById('create-task-option').addEventListener('click', () => showTaskForm());
        document.getElementById('create-co-option').addEventListener('click', () => showChangeOrderForm());
        document.getElementById('create-invoice-option').addEventListener('click', () => showInvoiceForm());
    }

    // =============================================
    // CRUD FORMS
    // =============================================
    function showLeadForm(lead = null) {
        const isEdit = !!lead;
        openModal(isEdit ? 'Edit Lead' : 'New Lead', `
            <form class="modal-form" id="lead-form">
                <div class="form-group">
                    <label>Project Title</label>
                    <input type="text" class="form-input" name="title" value="${lead?.title || ''}" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" class="form-input" name="client" value="${lead?.client || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Estimated Value</label>
                        <input type="text" class="form-input" name="value" value="${lead?.value || ''}" placeholder="$50k" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-select" name="status">
                        <option value="New" ${lead?.status === 'New' ? 'selected' : ''}>New</option>
                        <option value="Contacted" ${lead?.status === 'Contacted' ? 'selected' : ''}>Contacted</option>
                        <option value="Estimate Sent" ${lead?.status === 'Estimate Sent' ? 'selected' : ''}>Estimate Sent</option>
                        <option value="Won" ${lead?.status === 'Won' ? 'selected' : ''}>Won</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="secondary-btn" onclick="document.getElementById('global-modal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="primary-btn" style="padding:8px 20px;">${isEdit ? 'Update' : 'Create'} Lead</button>
                </div>
            </form>
        `);

        document.getElementById('lead-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const obj = Object.fromEntries(fd);
            if (isEdit) {
                store.update('leads', lead.id, obj);
            } else {
                store.create('leads', obj);
            }
            closeModal();
            loadView(state.currentView);
            generateNotifications();
        });
    }

    function showProjectForm(project = null) {
        const isEdit = !!project;
        openModal(isEdit ? 'Edit Project' : 'New Project', `
            <form class="modal-form" id="project-form">
                <div class="form-group">
                    <label>Project Name</label>
                    <input type="text" class="form-input" name="name" value="${project?.name || ''}" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Client</label>
                        <input type="text" class="form-input" name="client" value="${project?.client || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-select" name="status">
                            <option value="Active" ${project?.status === 'Active' ? 'selected' : ''}>Active</option>
                            <option value="Pending" ${project?.status === 'Pending' ? 'selected' : ''}>Pending</option>
                            <option value="Completed" ${project?.status === 'Completed' ? 'selected' : ''}>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-input" name="startDate" value="${project?.startDate || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" class="form-input" name="endDate" value="${project?.endDate || ''}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Progress (%)</label>
                    <input type="number" class="form-input" name="progress" min="0" max="100" value="${project?.progress ?? 0}">
                </div>
                <div class="form-actions">
                    <button type="button" class="secondary-btn" onclick="document.getElementById('global-modal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="primary-btn" style="padding:8px 20px;">${isEdit ? 'Update' : 'Create'} Project</button>
                </div>
            </form>
        `);

        document.getElementById('project-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const obj = Object.fromEntries(fd);
            obj.progress = parseInt(obj.progress) || 0;
            if (isEdit) {
                store.update('projects', project.id, obj);
            } else {
                store.create('projects', obj);
            }
            closeModal();
            loadView(state.currentView);
            generateNotifications();
        });
    }

    function showTaskForm(task = null) {
        const isEdit = !!task;
        const projects = store.getAll('projects');
        const projectOptions = projects.map(p =>
            `<option value="${p.name}" ${task?.project === p.name ? 'selected' : ''}>${p.name}</option>`
        ).join('');

        openModal(isEdit ? 'Edit Task' : 'New Task', `
            <form class="modal-form" id="task-form">
                <div class="form-group">
                    <label>Task Title</label>
                    <input type="text" class="form-input" name="title" value="${task?.title || ''}" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Project</label>
                        <select class="form-select" name="project">
                            <option value="">Select project</option>
                            ${projectOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select class="form-select" name="priority">
                            <option value="high" ${task?.priority === 'high' ? 'selected' : ''}>High</option>
                            <option value="med" ${task?.priority === 'med' ? 'selected' : ''}>Medium</option>
                            <option value="low" ${task?.priority === 'low' ? 'selected' : ''}>Low</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" class="form-input" name="due" value="${task?.due || ''}" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="secondary-btn" onclick="document.getElementById('global-modal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="primary-btn" style="padding:8px 20px;">${isEdit ? 'Update' : 'Create'} Task</button>
                </div>
            </form>
        `);

        document.getElementById('task-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const obj = Object.fromEntries(fd);
            obj.completed = task?.completed || false;
            if (isEdit) {
                store.update('tasks', task.id, obj);
            } else {
                store.create('tasks', obj);
            }
            closeModal();
            loadView(state.currentView);
            generateNotifications();
        });
    }

    function showInvoiceForm(invoice = null) {
        const isEdit = !!invoice;
        const projects = store.getAll('projects');
        const projectOptions = projects.map(p =>
            `<option value="${p.name}" ${invoice?.project === p.name ? 'selected' : ''}>${p.name}</option>`
        ).join('');

        const nextNum = isEdit ? invoice.invoiceNumber : `INV-2026-${String(store.getAll('invoices').length + 45).padStart(3, '0')}`;

        openModal(isEdit ? 'Edit Invoice' : 'New Invoice', `
            <form class="modal-form" id="invoice-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Invoice #</label>
                        <input type="text" class="form-input" name="invoiceNumber" value="${nextNum}" ${isEdit ? '' : 'readonly'}>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-select" name="status">
                            <option value="Pending" ${invoice?.status === 'Pending' ? 'selected' : ''}>Pending</option>
                            <option value="Paid" ${invoice?.status === 'Paid' ? 'selected' : ''}>Paid</option>
                            <option value="Overdue" ${invoice?.status === 'Overdue' ? 'selected' : ''}>Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Project</label>
                        <select class="form-select" name="project">
                            <option value="">Select project</option>
                            ${projectOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount ($)</label>
                        <input type="number" class="form-input" name="amount" step="0.01" value="${invoice?.amount || ''}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Issue Date</label>
                    <input type="date" class="form-input" name="date" value="${invoice?.date || new Date().toISOString().slice(0,10)}" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="secondary-btn" onclick="document.getElementById('global-modal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="primary-btn" style="padding:8px 20px;">${isEdit ? 'Update' : 'Create'} Invoice</button>
                </div>
            </form>
        `);

        document.getElementById('invoice-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const obj = Object.fromEntries(fd);
            obj.amount = parseFloat(obj.amount) || 0;
            if (isEdit) {
                store.update('invoices', invoice.id, obj);
            } else {
                store.create('invoices', obj);
            }
            closeModal();
            loadView(state.currentView);
            generateNotifications();
        });
    }

    function showChangeOrderForm(co = null) {
        const isEdit = !!co;
        const projects = store.getAll('projects');
        const projectOptions = projects.map(p =>
            `<option value="${p.name}" ${co?.project === p.name ? 'selected' : ''}>${p.name}</option>`
        ).join('');

        const nextNum = isEdit ? co.coNumber : `CO-${String(store.getAll('changeOrders').length + 105).padStart(3, '0')}`;

        openModal(isEdit ? 'Edit Change Order' : 'New Change Order', `
            <form class="modal-form" id="co-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>CO Number</label>
                        <input type="text" class="form-input" name="coNumber" value="${nextNum}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-select" name="status">
                            <option value="Pending Approval" ${co?.status === 'Pending Approval' ? 'selected' : ''}>Pending Approval</option>
                            <option value="Approved" ${co?.status === 'Approved' ? 'selected' : ''}>Approved</option>
                            <option value="Rejected" ${co?.status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Project</label>
                    <select class="form-select" name="project">
                        <option value="">Select project</option>
                        ${projectOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" class="form-input" name="desc" value="${co?.desc || ''}" placeholder="Describe the change..." required>
                </div>
                <div class="form-group">
                    <label>Amount (e.g. +$3,200 or -$1,500)</label>
                    <input type="text" class="form-input" name="amount" value="${co?.amount || ''}" placeholder="+$0" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="secondary-btn" onclick="document.getElementById('global-modal').classList.remove('open')">Cancel</button>
                    <button type="submit" class="primary-btn" style="padding:8px 20px;">${isEdit ? 'Update' : 'Create'} Change Order</button>
                </div>
            </form>
        `);

        document.getElementById('co-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const obj = Object.fromEntries(fd);
            if (isEdit) {
                store.update('changeOrders', co.id, obj);
            } else {
                store.create('changeOrders', obj);
            }
            closeModal();
            loadView(state.currentView);
            generateNotifications();
        });
    }

    function showEstimatePreview(lead) {
        openModal(`Estimate — ${lead.title}`, `
            <div class="estimate-preview">
                <div class="estimate-preview-header">
                    <h2>StructCRM Estimate</h2>
                    <p>${lead.title} — ${lead.client}</p>
                </div>
                <div class="estimate-preview-body">
                    <table class="estimate-line-items">
                        <thead><tr><th>Description</th><th style="text-align:right;">Amount</th></tr></thead>
                        <tbody>
                            <tr><td>Materials</td><td style="text-align:right;">${lead.value}</td></tr>
                            <tr><td>Labor (estimated)</td><td style="text-align:right;">—</td></tr>
                            <tr class="estimate-total-row"><td>Estimated Total</td><td style="text-align:right;">${lead.value}</td></tr>
                        </tbody>
                    </table>
                    <div class="estimate-footer">
                        <p>This is a preliminary estimate. Final pricing may vary based on site conditions and material availability.</p>
                    </div>
                    <div class="form-actions" style="margin-top:16px;">
                        <button class="secondary-btn" onclick="document.getElementById('global-modal').classList.remove('open')">Close</button>
                        <button class="primary-btn" style="padding:8px 20px;" onclick="window.print()"><i data-lucide="printer" style="width:16px;height:16px;"></i> Print / Save PDF</button>
                    </div>
                </div>
            </div>
        `);
    }

    // Confirmation dialog
    function showConfirm(message, onConfirm) {
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';
        overlay.innerHTML = `
            <div class="confirm-dialog">
                <h4>Confirm Action</h4>
                <p>${message}</p>
                <div class="confirm-actions">
                    <button class="secondary-btn cancel-confirm">Cancel</button>
                    <button class="danger-btn confirm-yes">Delete</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        overlay.querySelector('.cancel-confirm').addEventListener('click', () => overlay.remove());
        overlay.querySelector('.confirm-yes').addEventListener('click', () => {
            onConfirm();
            overlay.remove();
        });
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.remove();
        });
    }

    // =============================================
    // NAVIGATION & VIEW LOADING
    // =============================================
    el.mobileBtn.addEventListener('click', () => {
        el.sidebar.classList.toggle('open');
    });

    el.navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const view = item.getAttribute('data-view');
            el.navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            el.viewTitle.textContent = item.textContent.trim();
            if (window.innerWidth <= 768) el.sidebar.classList.remove('open');
            loadView(view);
        });
    });

    function loadView(viewId) {
        state.currentView = viewId;
        const template = document.getElementById(`view-${viewId}`);
        if (!template) return;

        el.contentArea.style.opacity = 0;
        setTimeout(() => {
            el.contentArea.innerHTML = '';
            el.contentArea.appendChild(template.content.cloneNode(true));
            lucide.createIcons();

            const inits = {
                'dashboard': initDashboard,
                'leads': initLeads,
                'projects': initProjects,
                'project-detail': () => initProjectDetail(state._detailProjectId),
                'tasks': initTasks,
                'change-orders': initChangeOrders,
                'photos': initPhotos,
                'invoices': initInvoices,
                'client-portal': initClientPortal,
                'settings': initSettings
            };
            if (inits[viewId]) inits[viewId]();

            el.contentArea.style.transition = 'opacity 0.3s ease';
            el.contentArea.style.opacity = 1;
        }, 150);
    }

    // =============================================
    // DASHBOARD
    // =============================================
    function initDashboard() {
        const projects = store.getAll('projects');
        const tasks = store.getAll('tasks');
        const leads = store.getAll('leads');
        const invoices = store.getAll('invoices');
        const changeOrders = store.getAll('changeOrders');

        const activeProjects = projects.filter(p => p.status === 'Active').length;
        const pendingEstimates = leads.filter(l => l.status === 'Estimate Sent').length;
        const tasksDueToday = tasks.filter(t => !t.completed).length;
        const pendingCOs = changeOrders.filter(c => c.status === 'Pending Approval').length;

        const statsContainer = document.getElementById('dashboard-stats');
        if (statsContainer) {
            statsContainer.innerHTML = `
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--accent-soft);color:var(--accent);"><i data-lucide="briefcase"></i></div>
                    <div class="stat-details"><span class="stat-label">Active Projects</span><h3 class="stat-value">${activeProjects}</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--success-soft);color:var(--success);"><i data-lucide="dollar-sign"></i></div>
                    <div class="stat-details"><span class="stat-label">Pending Estimates</span><h3 class="stat-value">${pendingEstimates}</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--warning-soft);color:var(--warning);"><i data-lucide="check-square"></i></div>
                    <div class="stat-details"><span class="stat-label">Open Tasks</span><h3 class="stat-value">${tasksDueToday}</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--danger-soft);color:var(--danger);"><i data-lucide="alert-circle"></i></div>
                    <div class="stat-details"><span class="stat-label">Change Orders</span><h3 class="stat-value">${pendingCOs}</h3></div>
                </div>
            `;
            lucide.createIcons({ nodes: [statsContainer] });
        }

        // Recent activity
        const activityList = document.getElementById('dashboard-activity');
        const recentItems = [
            ...changeOrders.slice(-2).map(c => ({ icon: 'file-diff', text: `${c.coNumber}: ${c.desc}`, time: c.status })),
            ...leads.slice(-2).map(l => ({ icon: 'user-plus', text: `Lead: ${l.title} — ${l.client}`, time: l.status }))
        ].slice(0, 4);

        if (activityList) {
            activityList.innerHTML = recentItems.length ? recentItems.map(act => `
                <li class="activity-item">
                    <div class="activity-icon"><i data-lucide="${act.icon}"></i></div>
                    <div class="activity-content">
                        <p>${act.text}</p>
                        <span class="activity-time">${act.time}</span>
                    </div>
                </li>
            `).join('') : '<li class="activity-item"><div class="activity-content"><p style="color:var(--text-muted)">No recent activity</p></div></li>';
            lucide.createIcons({ nodes: [activityList] });
        }

        // Upcoming tasks
        const taskList = document.getElementById('dashboard-tasks');
        const upcomingTasks = tasks.filter(t => !t.completed).sort((a, b) => a.due.localeCompare(b.due)).slice(0, 4);
        if (taskList) {
            taskList.innerHTML = upcomingTasks.length ? upcomingTasks.map(task => `
                <li class="activity-item">
                    <div class="activity-icon"><i data-lucide="check-square"></i></div>
                    <div class="activity-content">
                        <p>${task.title}</p>
                        <span class="activity-time">${formatDate(task.due)} · ${task.project || 'No project'}</span>
                    </div>
                </li>
            `).join('') : '<li class="activity-item"><div class="activity-content"><p style="color:var(--text-muted)">No upcoming tasks</p></div></li>';
            lucide.createIcons({ nodes: [taskList] });
        }

        // Charts
        renderRevenueChart(invoices);
        renderStatusChart(projects);
    }

    // =============================================
    // CHARTS (Canvas API)
    // =============================================
    function renderRevenueChart(invoices) {
        const canvas = document.getElementById('revenue-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = 220 * dpr;
        canvas.style.height = '220px';
        ctx.scale(dpr, dpr);

        const w = rect.width;
        const h = 220;

        // Group invoices by month
        const months = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthMap = { '07': 0, '08': 1, '09': 2, '10': 3, '11': 4, '12': 5 };
        const paidByMonth = new Array(6).fill(0);
        const pendingByMonth = new Array(6).fill(0);

        invoices.forEach(inv => {
            const m = inv.date.slice(5, 7);
            const idx = monthMap[m];
            if (idx !== undefined) {
                if (inv.status === 'Paid') paidByMonth[idx] += inv.amount;
                else pendingByMonth[idx] += inv.amount;
            }
        });

        const maxVal = Math.max(...paidByMonth.map((v, i) => v + pendingByMonth[i]), 1);
        const barAreaWidth = w - 60;
        const barGroupWidth = barAreaWidth / months.length;
        const barWidth = barGroupWidth * 0.35;
        const chartHeight = h - 50;
        const originY = h - 30;
        const originX = 50;

        // Grid lines
        ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || '#E2E8F0';
        ctx.lineWidth = 0.5;
        for (let i = 0; i <= 4; i++) {
            const y = originY - (chartHeight / 4) * i;
            ctx.beginPath();
            ctx.moveTo(originX, y);
            ctx.lineTo(w, y);
            ctx.stroke();

            ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim() || '#64748B';
            ctx.font = '11px Outfit';
            ctx.textAlign = 'right';
            ctx.fillText('$' + Math.round((maxVal / 4) * i / 1000) + 'k', originX - 8, y + 4);
        }

        // Bars
        months.forEach((label, i) => {
            const x = originX + i * barGroupWidth + barGroupWidth / 2;

            // Paid bar
            const paidH = (paidByMonth[i] / maxVal) * chartHeight;
            const roundRadius = 4;
            ctx.fillStyle = '#2563EB';
            drawRoundedBar(ctx, x - barWidth - 1, originY - paidH, barWidth, paidH, roundRadius);

            // Pending bar
            const pendH = (pendingByMonth[i] / maxVal) * chartHeight;
            ctx.fillStyle = '#94A3B8';
            drawRoundedBar(ctx, x + 1, originY - pendH, barWidth, pendH, roundRadius);

            // Label
            ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim() || '#64748B';
            ctx.font = '12px Outfit';
            ctx.textAlign = 'center';
            ctx.fillText(label, x, originY + 18);
        });

        // Legend
        const legendContainer = document.getElementById('revenue-legend');
        if (legendContainer) {
            legendContainer.innerHTML = `
                <div class="legend-item"><span class="legend-dot" style="background:#2563EB;"></span> Paid</div>
                <div class="legend-item"><span class="legend-dot" style="background:#94A3B8;"></span> Pending/Overdue</div>
            `;
        }
    }

    function drawRoundedBar(ctx, x, y, w, h, r) {
        if (h <= 0) return;
        r = Math.min(r, h / 2, w / 2);
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h);
        ctx.lineTo(x, y + h);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.fill();
    }

    function renderStatusChart(projects) {
        const canvas = document.getElementById('status-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        canvas.width = 220 * dpr;
        canvas.height = 220 * dpr;
        canvas.style.width = '220px';
        canvas.style.height = '220px';
        ctx.scale(dpr, dpr);

        const cx = 110, cy = 110, radius = 85, innerRadius = 55;
        const statuses = [
            { key: 'Active', color: '#2563EB' },
            { key: 'Pending', color: '#F59E0B' },
            { key: 'Completed', color: '#10B981' }
        ];

        const counts = statuses.map(s => ({
            ...s,
            count: projects.filter(p => p.status === s.key).length
        }));

        const total = counts.reduce((s, c) => s + c.count, 0) || 1;
        let startAngle = -Math.PI / 2;

        counts.forEach(item => {
            const sliceAngle = (item.count / total) * 2 * Math.PI;
            ctx.beginPath();
            ctx.arc(cx, cy, radius, startAngle, startAngle + sliceAngle);
            ctx.arc(cx, cy, innerRadius, startAngle + sliceAngle, startAngle, true);
            ctx.closePath();
            ctx.fillStyle = item.color;
            ctx.fill();
            startAngle += sliceAngle;
        });

        // Center label
        const donutTotal = document.getElementById('donut-total');
        if (donutTotal) donutTotal.textContent = projects.length;

        // Legend
        const legendContainer = document.getElementById('status-legend');
        if (legendContainer) {
            legendContainer.innerHTML = counts.map(c =>
                `<div class="legend-item"><span class="legend-dot" style="background:${c.color};"></span> ${c.key} (${c.count})</div>`
            ).join('');
        }
    }

    // =============================================
    // LEADS (Kanban with Drag & Drop)
    // =============================================
    function initLeads() {
        const leads = store.getAll('leads');
        const statusColumns = {
            'New': 'leads-new',
            'Contacted': 'leads-contacted',
            'Estimate Sent': 'leads-estimate',
            'Won': 'leads-won'
        };
        const countIds = {
            'New': 'count-new',
            'Contacted': 'count-contacted',
            'Estimate Sent': 'count-estimate',
            'Won': 'count-won'
        };

        Object.entries(statusColumns).forEach(([status, containerId]) => {
            const container = document.getElementById(containerId);
            const countEl = document.getElementById(countIds[status]);
            const filtered = leads.filter(l => l.status === status);
            if (countEl) countEl.textContent = filtered.length;

            if (container) {
                container.innerHTML = filtered.map(lead => `
                    <div class="kanban-card" draggable="true" data-id="${lead.id}">
                        <h5>${lead.title}</h5>
                        <p>${lead.client}</p>
                        <div class="kanban-card-footer">
                            <span class="kanban-card-value">${lead.value}</span>
                            <div class="kanban-card-actions">
                                <button class="icon-btn estimate-lead-btn" data-id="${lead.id}" title="View Estimate"><i data-lucide="file-text"></i></button>
                                <button class="icon-btn edit-lead-btn" data-id="${lead.id}" title="Edit"><i data-lucide="edit-2"></i></button>
                                <button class="icon-btn delete-lead-btn" data-id="${lead.id}" title="Delete"><i data-lucide="trash-2"></i></button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        });

        lucide.createIcons();
        initKanbanDragDrop();

        // Estimate preview
        document.querySelectorAll('.estimate-lead-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const lead = store.getById('leads', btn.dataset.id);
                if (lead) showEstimatePreview(lead);
            });
        });

        // Edit / Delete listeners
        document.querySelectorAll('.edit-lead-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const lead = store.getById('leads', btn.dataset.id);
                if (lead) showLeadForm(lead);
            });
        });

        document.querySelectorAll('.delete-lead-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                showConfirm('Are you sure you want to delete this lead?', () => {
                    store.delete('leads', btn.dataset.id);
                    loadView('leads');
                    generateNotifications();
                });
            });
        });
    }

    function initKanbanDragDrop() {
        const cards = document.querySelectorAll('.kanban-card[draggable]');
        const columns = document.querySelectorAll('.kanban-column');

        cards.forEach(card => {
            card.addEventListener('dragstart', (e) => {
                card.classList.add('dragging');
                e.dataTransfer.setData('text/plain', card.dataset.id);
                e.dataTransfer.effectAllowed = 'move';
            });
            card.addEventListener('dragend', () => {
                card.classList.remove('dragging');
                columns.forEach(col => col.classList.remove('drag-over'));
            });
        });

        columns.forEach(col => {
            const content = col.querySelector('.column-content');
            col.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                col.classList.add('drag-over');
            });
            col.addEventListener('dragleave', (e) => {
                if (!col.contains(e.relatedTarget)) {
                    col.classList.remove('drag-over');
                }
            });
            col.addEventListener('drop', (e) => {
                e.preventDefault();
                col.classList.remove('drag-over');
                const id = e.dataTransfer.getData('text/plain');
                const newStatus = col.dataset.status;
                if (id && newStatus) {
                    store.update('leads', id, { status: newStatus });
                    initLeads();
                    generateNotifications();
                }
            });
        });
    }

    // =============================================
    // PROJECTS
    // =============================================
    function initProjects() {
        const tbody = document.getElementById('projects-table-body');
        const projects = store.getAll('projects');

        tbody.innerHTML = projects.map(p => {
            const timeline = p.startDate && p.endDate
                ? `${formatDate(p.startDate)} — ${formatDate(p.endDate)}`
                : '—';
            return `
            <tr>
                <td><strong><a href="#" class="project-name-link" data-id="${p.id}" style="color:var(--accent);text-decoration:none;">${p.name}</a></strong></td>
                <td>${p.client}</td>
                <td><span class="status-badge ${p.status.toLowerCase()}">${p.status}</span></td>
                <td>
                    <div class="td-progress">
                        <div class="progress-track"><div class="progress-fill" style="width:${p.progress}%"></div></div>
                        <span>${p.progress}%</span>
                    </div>
                </td>
                <td>${timeline}</td>
                <td>
                    <div class="table-actions">
                        <button class="icon-btn view-project-btn" data-id="${p.id}" title="View Details"><i data-lucide="eye"></i></button>
                        <button class="icon-btn edit-project-btn" data-id="${p.id}" title="Edit"><i data-lucide="edit-2"></i></button>
                        <button class="icon-btn delete-project-btn" data-id="${p.id}" title="Delete"><i data-lucide="trash-2"></i></button>
                    </div>
                </td>
            </tr>
        `}).join('');

        lucide.createIcons();

        // Click project name or view button to open detail
        document.querySelectorAll('.project-name-link, .view-project-btn').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                openProjectDetail(el.dataset.id);
            });
        });

        document.querySelectorAll('.edit-project-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const proj = store.getById('projects', btn.dataset.id);
                if (proj) showProjectForm(proj);
            });
        });

        document.querySelectorAll('.delete-project-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                showConfirm('Delete this project and all associated data?', () => {
                    store.delete('projects', btn.dataset.id);
                    loadView('projects');
                    generateNotifications();
                });
            });
        });
    }

    function openProjectDetail(projectId) {
        state._detailProjectId = projectId;
        el.viewTitle.textContent = 'Project Details';
        el.navItems.forEach(n => n.classList.remove('active'));
        loadView('project-detail');
    }

    function initProjectDetail(projectId) {
        const container = document.getElementById('project-detail-container');
        if (!container || !projectId) return;

        const project = store.getById('projects', projectId);
        if (!project) {
            container.innerHTML = '<div class="empty-state"><h4>Project not found</h4></div>';
            return;
        }

        const tasks = store.getAll('tasks').filter(t => t.project === project.name);
        const cos = store.getAll('changeOrders').filter(c => c.project === project.name);
        const invoices = store.getAll('invoices').filter(i => i.project === project.name);
        const photos = JSON.parse(localStorage.getItem('structcrm_photos_' + projectId) || '[]');

        const totalInvoiced = invoices.reduce((s, i) => s + (Number(i.amount) || 0), 0);
        const paidInvoices = invoices.filter(i => i.status === 'Paid').reduce((s, i) => s + (Number(i.amount) || 0), 0);

        container.innerHTML = `
            <div class="project-detail-header">
                <div class="detail-top-row">
                    <div>
                        <h2 class="detail-title">${project.name}</h2>
                        <p class="detail-client"><i data-lucide="user" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>${project.client}</p>
                    </div>
                    <button class="detail-back-btn" id="back-to-projects">
                        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Back to Projects
                    </button>
                </div>
                <div class="detail-progress-bar">
                    <div class="fill" style="width:${project.progress}%;"></div>
                </div>
                <div class="detail-meta-grid">
                    <div class="detail-meta-item">
                        <label>Status</label>
                        <span><span class="status-badge ${project.status.toLowerCase()}">${project.status}</span></span>
                    </div>
                    <div class="detail-meta-item">
                        <label>Progress</label>
                        <span>${project.progress}%</span>
                    </div>
                    <div class="detail-meta-item">
                        <label>Timeline</label>
                        <span>${project.startDate ? formatDate(project.startDate) : '—'} — ${project.endDate ? formatDate(project.endDate) : '—'}</span>
                    </div>
                    <div class="detail-meta-item">
                        <label>Total Invoiced</label>
                        <span>$${totalInvoiced.toLocaleString()}</span>
                    </div>
                    <div class="detail-meta-item">
                        <label>Collected</label>
                        <span style="color:var(--success);">$${paidInvoices.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="detail-tabs">
                <button class="detail-tab active" data-tab="overview"><i data-lucide="layout-dashboard" style="width:16px;height:16px;"></i> Overview</button>
                <button class="detail-tab" data-tab="tasks"><i data-lucide="check-square" style="width:16px;height:16px;"></i> Tasks <span class="tab-count">${tasks.length}</span></button>
                <button class="detail-tab" data-tab="change-orders"><i data-lucide="file-diff" style="width:16px;height:16px;"></i> Change Orders <span class="tab-count">${cos.length}</span></button>
                <button class="detail-tab" data-tab="invoices"><i data-lucide="receipt" style="width:16px;height:16px;"></i> Invoices <span class="tab-count">${invoices.length}</span></button>
                <button class="detail-tab" data-tab="photos"><i data-lucide="camera" style="width:16px;height:16px;"></i> Photos <span class="tab-count">${photos.length}</span></button>
            </div>

            <div class="detail-tab-content active" id="tab-overview">
                <div class="dashboard-widgets" style="grid-template-columns:1fr 1fr;">
                    <div class="widget">
                        <h3>Recent Tasks</h3>
                        ${tasks.length ? tasks.slice(0, 5).map(t => `
                            <div class="activity-item" style="padding:10px 0;border-bottom:1px solid var(--border);">
                                <div class="activity-icon" style="width:28px;height:28px;">
                                    <i data-lucide="${t.completed ? 'check-circle' : 'circle'}" style="width:16px;height:16px;color:${t.completed ? 'var(--success)' : 'var(--text-muted)'};"></i>
                                </div>
                                <div class="activity-content">
                                    <p style="${t.completed ? 'text-decoration:line-through;opacity:0.6' : ''}">${t.title}</p>
                                    <span class="activity-time">${formatDate(t.due)}</span>
                                </div>
                            </div>
                        `).join('') : '<p style="color:var(--text-muted);padding:16px 0;">No tasks assigned yet.</p>'}
                    </div>
                    <div class="widget">
                        <h3>Financial Summary</h3>
                        <div style="padding:12px 0;">
                            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
                                <span style="color:var(--text-muted);">Total Invoiced</span>
                                <strong>$${totalInvoiced.toLocaleString()}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
                                <span style="color:var(--text-muted);">Collected</span>
                                <strong style="color:var(--success);">$${paidInvoices.toLocaleString()}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:8px 0;">
                                <span style="color:var(--text-muted);">Outstanding</span>
                                <strong style="color:var(--danger);">$${(totalInvoiced - paidInvoices).toLocaleString()}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-tab-content" id="tab-tasks">
                <div style="margin-bottom:16px;"><button class="primary-btn" style="padding:8px 16px;" id="detail-add-task"><i data-lucide="plus" style="width:16px;height:16px;"></i> Add Task</button></div>
                <div class="task-grid" id="detail-tasks-grid">${tasks.map(t => `
                    <div class="card-box priority-${t.priority}" style="${t.completed ? 'opacity:0.5' : ''}">
                        <div class="card-meta">
                            <span style="color:var(--text-muted);text-transform:uppercase;">${t.priority}</span>
                            <span style="font-weight:600;color:var(--accent);">${formatDate(t.due)}</span>
                        </div>
                        <h4 style="${t.completed ? 'text-decoration:line-through' : ''}">${t.title}</h4>
                    </div>
                `).join('') || '<div class="empty-state"><i data-lucide="check-square" class="empty-state-icon"></i><h4>No Tasks</h4><p>Add tasks to track progress on this project.</p></div>'}</div>
            </div>

            <div class="detail-tab-content" id="tab-change-orders">
                <div style="margin-bottom:16px;"><button class="primary-btn" style="padding:8px 16px;" id="detail-add-co"><i data-lucide="plus" style="width:16px;height:16px;"></i> Add Change Order</button></div>
                <div class="cards-grid">${cos.map(c => `
                    <div class="card-box" style="border-left:4px solid ${c.status === 'Approved' ? 'var(--success)' : 'var(--warning)'}">
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <strong>${c.coNumber}</strong>
                            <span class="status-badge ${c.status === 'Approved' ? 'active' : 'pending'}">${c.status}</span>
                        </div>
                        <p style="font-weight:500;margin-bottom:8px;">${c.desc}</p>
                        <span style="font-size:1.1rem;font-weight:700;">${c.amount}</span>
                    </div>
                `).join('') || '<div class="empty-state"><i data-lucide="file-diff" class="empty-state-icon"></i><h4>No Change Orders</h4><p>Track scope modifications here.</p></div>'}</div>
            </div>

            <div class="detail-tab-content" id="tab-invoices">
                <div style="margin-bottom:16px;"><button class="primary-btn" style="padding:8px 16px;" id="detail-add-invoice"><i data-lucide="plus" style="width:16px;height:16px;"></i> Add Invoice</button></div>
                <div class="data-table-container"><table class="data-table">
                    <thead><tr><th>Invoice #</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>${invoices.map(inv => `
                        <tr>
                            <td><strong>${inv.invoiceNumber}</strong></td>
                            <td style="font-weight:600;">$${Number(inv.amount).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            <td>${formatDate(inv.date)}</td>
                            <td><span class="status-badge ${inv.status.toLowerCase()}">${inv.status}</span></td>
                        </tr>
                    `).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:32px;">No invoices yet.</td></tr>'}</tbody>
                </table></div>
            </div>

            <div class="detail-tab-content" id="tab-photos">
                <div class="photo-upload-zone" id="detail-photo-upload">
                    <i data-lucide="upload-cloud" class="upload-icon"></i>
                    <h4>Upload Site Photos</h4>
                    <p>Drag & drop images here or click to browse</p>
                    <input type="file" id="detail-photo-input" accept="image/*" multiple>
                </div>
                <div class="photo-gallery" id="detail-photo-gallery">${photos.map((p, i) => `
                    <div class="photo-item">
                        <img src="${p.data}" alt="${p.label}" loading="lazy">
                        <button class="photo-delete-btn" data-index="${i}"><i data-lucide="x" style="width:14px;height:14px;"></i></button>
                        <div class="photo-overlay">
                            <span style="font-weight:600;font-size:0.9rem;">${p.label}</span>
                            <p style="font-size:0.75rem;margin-top:4px;">Added ${p.date}</p>
                        </div>
                    </div>
                `).join('') || '<div class="empty-state" style="grid-column:1/-1;"><i data-lucide="camera" class="empty-state-icon"></i><h4>No Photos Yet</h4><p>Upload site photos to document progress.</p></div>'}</div>
            </div>
        `;

        lucide.createIcons();

        // Back button
        document.getElementById('back-to-projects')?.addEventListener('click', () => {
            el.viewTitle.textContent = 'Projects';
            el.navItems.forEach(n => {
                n.classList.remove('active');
                if (n.dataset.view === 'projects') n.classList.add('active');
            });
            loadView('projects');
        });

        // Tabs
        container.querySelectorAll('.detail-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                container.querySelectorAll('.detail-tab').forEach(t => t.classList.remove('active'));
                container.querySelectorAll('.detail-tab-content').forEach(tc => tc.classList.remove('active'));
                tab.classList.add('active');
                const target = document.getElementById('tab-' + tab.dataset.tab);
                if (target) target.classList.add('active');
            });
        });

        // Add buttons
        document.getElementById('detail-add-task')?.addEventListener('click', () => {
            const t = { project: project.name };
            showTaskForm(null);
            // Pre-fill project after modal opens
            setTimeout(() => {
                const projSelect = document.querySelector('#task-form select[name="project"]');
                if (projSelect) projSelect.value = project.name;
            }, 100);
        });

        document.getElementById('detail-add-co')?.addEventListener('click', () => {
            showChangeOrderForm(null);
            setTimeout(() => {
                const projSelect = document.querySelector('#co-form select[name="project"]');
                if (projSelect) projSelect.value = project.name;
            }, 100);
        });

        document.getElementById('detail-add-invoice')?.addEventListener('click', () => {
            showInvoiceForm(null);
            setTimeout(() => {
                const projSelect = document.querySelector('#invoice-form select[name="project"]');
                if (projSelect) projSelect.value = project.name;
            }, 100);
        });

        // Photo upload
        initDetailPhotoUpload(projectId, project.name);
    }

    function initDetailPhotoUpload(projectId, projectName) {
        const uploadZone = document.getElementById('detail-photo-upload');
        const fileInput = document.getElementById('detail-photo-input');
        const gallery = document.getElementById('detail-photo-gallery');
        if (!uploadZone || !fileInput) return;

        ['dragover', 'dragenter'].forEach(event => {
            uploadZone.addEventListener(event, (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(event => {
            uploadZone.addEventListener(event, () => {
                uploadZone.classList.remove('dragover');
            });
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            handlePhotoFiles(e.dataTransfer.files, projectId);
        });

        fileInput.addEventListener('change', (e) => {
            handlePhotoFiles(e.target.files, projectId);
        });

        // Delete buttons
        gallery?.querySelectorAll('.photo-delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const photos = JSON.parse(localStorage.getItem('structcrm_photos_' + projectId) || '[]');
                photos.splice(parseInt(btn.dataset.index), 1);
                localStorage.setItem('structcrm_photos_' + projectId, JSON.stringify(photos));
                initProjectDetail(projectId);
            });
        });
    }

    function handlePhotoFiles(files, projectId) {
        const photos = JSON.parse(localStorage.getItem('structcrm_photos_' + projectId) || '[]');
        const maxPhotos = 20; // Limit to prevent localStorage overflow

        Array.from(files).slice(0, maxPhotos - photos.length).forEach(file => {
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                // Resize image to save localStorage space
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const maxDim = 600;
                    let w = img.width, h = img.height;
                    if (w > maxDim || h > maxDim) {
                        if (w > h) { h = (h / w) * maxDim; w = maxDim; }
                        else { w = (w / h) * maxDim; h = maxDim; }
                    }
                    canvas.width = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    const compressed = canvas.toDataURL('image/jpeg', 0.7);

                    const currentPhotos = JSON.parse(localStorage.getItem('structcrm_photos_' + projectId) || '[]');
                    currentPhotos.push({
                        data: compressed,
                        label: file.name.replace(/\.[^/.]+$/, ''),
                        date: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
                    });
                    localStorage.setItem('structcrm_photos_' + projectId, JSON.stringify(currentPhotos));
                    initProjectDetail(projectId);
                    // Switch to photos tab
                    const photosTab = document.querySelector('.detail-tab[data-tab="photos"]');
                    if (photosTab) photosTab.click();
                    showToast(`Photo "${file.name}" uploaded!`);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // =============================================
    // TASKS
    // =============================================
    function initTasks() {
        const container = document.getElementById('tasks-container');
        const tasks = store.getAll('tasks');
        const projects = store.getAll('projects');

        // Populate project filter
        const filterSelect = document.getElementById('task-filter-project');
        if (filterSelect) {
            projects.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.textContent = p.name;
                filterSelect.appendChild(opt);
            });
            filterSelect.addEventListener('change', () => renderTasks());
        }

        const priorityFilter = document.getElementById('task-filter-priority');
        if (priorityFilter) {
            priorityFilter.addEventListener('change', () => renderTasks());
        }

        function renderTasks() {
            let filtered = [...tasks];
            const projVal = filterSelect?.value;
            const prioVal = priorityFilter?.value;
            if (projVal) filtered = filtered.filter(t => t.project === projVal);
            if (prioVal) filtered = filtered.filter(t => t.priority === prioVal);

            container.innerHTML = filtered.map(t => `
                <div class="card-box priority-${t.priority}" style="${t.completed ? 'opacity:0.5' : ''}">
                    <div class="card-meta">
                        <span style="color:var(--text-muted);text-transform:uppercase;">${t.project || 'Unassigned'}</span>
                        <span style="font-weight:600;color:var(--accent);">${formatDate(t.due)}</span>
                    </div>
                    <h4 style="${t.completed ? 'text-decoration:line-through' : ''}">${t.title}</h4>
                    <div class="card-actions">
                        ${!t.completed ? `<button class="btn-sm complete-task-btn" data-id="${t.id}" style="background:var(--success);color:white;border:none;"><i data-lucide="check" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"></i>Complete</button>` : '<span class="status-badge completed">Done</span>'}
                        <button class="btn-sm edit-task-btn" data-id="${t.id}" style="margin-top:0;"><i data-lucide="edit-2" style="width:14px;height:14px;vertical-align:middle;margin-right:2px;"></i>Edit</button>
                        <button class="btn-sm delete-task-btn" data-id="${t.id}" style="margin-top:0;color:var(--danger);"><i data-lucide="trash-2" style="width:14px;height:14px;vertical-align:middle;margin-right:2px;"></i>Delete</button>
                    </div>
                </div>
            `).join('') || '<p style="color:var(--text-muted);grid-column:1/-1;text-align:center;padding:40px;">No tasks found.</p>';

            lucide.createIcons();

            document.querySelectorAll('.complete-task-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    store.update('tasks', btn.dataset.id, { completed: true });
                    loadView('tasks');
                    generateNotifications();
                });
            });

            document.querySelectorAll('.edit-task-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const task = store.getById('tasks', btn.dataset.id);
                    if (task) showTaskForm(task);
                });
            });

            document.querySelectorAll('.delete-task-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    showConfirm('Delete this task?', () => {
                        store.delete('tasks', btn.dataset.id);
                        loadView('tasks');
                        generateNotifications();
                    });
                });
            });
        }

        renderTasks();
    }

    // =============================================
    // CHANGE ORDERS
    // =============================================
    function initChangeOrders() {
        const container = document.getElementById('co-container');
        const cos = store.getAll('changeOrders');

        container.innerHTML = cos.map(c => `
            <div class="card-box" style="border-left:4px solid ${c.status === 'Approved' ? 'var(--success)' : 'var(--warning)'}">
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <strong>${c.coNumber}</strong>
                    <span class="status-badge ${c.status === 'Approved' ? 'active' : 'pending'}">${c.status}</span>
                </div>
                <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:4px;">${c.project}</p>
                <p style="font-weight:500;margin-bottom:12px;">${c.desc}</p>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:1.1rem;font-weight:700;">${c.amount}</span>
                    <div style="display:flex;gap:8px;">
                        ${c.status === 'Pending Approval' ? `<button class="btn-sm approve-co-btn" data-id="${c.id}" style="margin-top:0;background:var(--success);color:white;border:none;">Approve</button>` : ''}
                        <button class="btn-sm delete-co-btn" data-id="${c.id}" style="margin-top:0;color:var(--danger);">Delete</button>
                    </div>
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.approve-co-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                store.update('changeOrders', btn.dataset.id, { status: 'Approved' });
                loadView('change-orders');
                generateNotifications();
            });
        });

        document.querySelectorAll('.delete-co-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                showConfirm('Delete this change order?', () => {
                    store.delete('changeOrders', btn.dataset.id);
                    loadView('change-orders');
                    generateNotifications();
                });
            });
        });
    }

    // =============================================
    // PHOTOS
    // =============================================
    function initPhotos() {
        const container = document.getElementById('photo-gallery');
        const photos = [
            { url: 'https://images.unsplash.com/photo-1541888086425-d81bb19240f5?auto=format&fit=crop&w=500&q=80', label: 'Site Prep', date: 'Oct 24' },
            { url: 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=500&q=80', label: 'Framing', date: 'Oct 22' },
            { url: 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=500&q=80', label: 'Foundation', date: 'Oct 20' },
            { url: 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=500&q=80', label: 'Interior', date: 'Oct 18' },
            { url: 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=500&q=80', label: 'Roofing', date: 'Oct 15' }
        ];

        container.innerHTML = photos.map(p => `
            <div class="photo-item">
                <img src="${p.url}" alt="${p.label}" loading="lazy">
                <div class="photo-overlay">
                    <span style="font-weight:600;font-size:0.9rem;">${p.label}</span>
                    <p style="font-size:0.75rem;margin-top:4px;">Added ${p.date}</p>
                </div>
            </div>
        `).join('');
    }

    // =============================================
    // INVOICES
    // =============================================
    function initInvoices() {
        const tbody = document.getElementById('invoices-table-body');
        const invoices = store.getAll('invoices');

        tbody.innerHTML = invoices.map(inv => `
            <tr>
                <td><strong>${inv.invoiceNumber}</strong></td>
                <td>${inv.project}</td>
                <td style="font-weight:600;">$${Number(inv.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                <td>${formatDate(inv.date)}</td>
                <td><span class="status-badge ${inv.status.toLowerCase()}">${inv.status}</span></td>
                <td>
                    <div class="table-actions">
                        <button class="icon-btn edit-invoice-btn" data-id="${inv.id}"><i data-lucide="edit-2"></i></button>
                        <button class="icon-btn delete-invoice-btn" data-id="${inv.id}"><i data-lucide="trash-2"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

        lucide.createIcons();

        document.querySelectorAll('.edit-invoice-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const inv = store.getById('invoices', btn.dataset.id);
                if (inv) showInvoiceForm(inv);
            });
        });

        document.querySelectorAll('.delete-invoice-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                showConfirm('Delete this invoice?', () => {
                    store.delete('invoices', btn.dataset.id);
                    loadView('invoices');
                    generateNotifications();
                });
            });
        });
    }

    // =============================================
    // CLIENT PORTAL
    // =============================================
    function initClientPortal() {
        const mockup = document.getElementById('portal-mockup');
        if (!mockup) return;

        const projects = store.getAll('projects');
        const activeProject = projects.find(p => p.status === 'Active') || projects[0];
        const changeOrders = store.getAll('changeOrders');
        const pendingCO = changeOrders.find(c => c.status === 'Pending Approval');

        if (!activeProject) {
            mockup.innerHTML = '<div style="padding:40px;text-align:center;color:var(--text-muted);">No projects to display.</div>';
            return;
        }

        mockup.innerHTML = `
            <div class="portal-header">
                <h3>${activeProject.name}</h3>
                <span class="badge success">${activeProject.status}</span>
            </div>
            <div class="portal-progress">
                <div class="progress-info">
                    <span>Project Completion</span>
                    <span>${activeProject.progress}%</span>
                </div>
                <div class="progress-bar"><div class="fill" style="width:${activeProject.progress}%;"></div></div>
            </div>
            <div class="portal-grid">
                <div class="portal-card">
                    <h4><i data-lucide="file-text"></i> Latest Updates</h4>
                    <p>Project is ${activeProject.progress}% complete. Current status: ${activeProject.status}.</p>
                    <span class="date">${formatDate(activeProject.startDate)} — ${formatDate(activeProject.endDate)}</span>
                </div>
                <div class="portal-card">
                    <h4><i data-lucide="file-diff"></i> Change Orders</h4>
                    ${pendingCO
                        ? `<p>${pendingCO.desc} — ${pendingCO.amount}</p><button class="btn-sm" style="margin-top:8px;">Review</button>`
                        : '<p style="color:var(--text-muted);">No pending change orders.</p>'
                    }
                </div>
            </div>
        `;
        lucide.createIcons({ nodes: [mockup] });
    }

    // =============================================
    // SETTINGS
    // =============================================
    function initSettings() {
        lucide.createIcons();

        const themeToggle = document.getElementById('settings-theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        const saveProfileBtn = document.getElementById('save-profile-btn');
        if (saveProfileBtn) {
            saveProfileBtn.addEventListener('click', () => {
                const name = document.getElementById('settings-name')?.value || 'Admin User';
                const role = document.getElementById('settings-role')?.value || 'Project Manager';
                // Update sidebar
                const sidebarName = document.querySelector('.user-name');
                const sidebarRole = document.querySelector('.user-role');
                if (sidebarName) sidebarName.textContent = name;
                if (sidebarRole) sidebarRole.textContent = role;
                localStorage.setItem('structcrm_profile', JSON.stringify({ name, role }));
                showToast('Profile saved!');
            });
        }

        const exportBtn = document.getElementById('export-data-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                const data = store.exportAll();
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `structcrm_backup_${new Date().toISOString().slice(0,10)}.json`;
                a.click();
                URL.revokeObjectURL(url);
                showToast('Data exported!');
            });
        }

        const importBtn = document.getElementById('import-data-btn');
        const importInput = document.getElementById('import-file-input');
        if (importBtn && importInput) {
            importBtn.addEventListener('click', () => importInput.click());
            importInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    try {
                        const json = JSON.parse(ev.target.result);
                        store.importAll(json);
                        loadView('settings');
                        generateNotifications();
                        showToast('Data imported successfully!');
                    } catch {
                        showToast('Invalid JSON file.');
                    }
                };
                reader.readAsText(file);
            });
        }

        const clearBtn = document.getElementById('clear-data-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                showConfirm('This will permanently delete ALL stored data. This cannot be undone.', () => {
                    store.clearAll();
                    loadView('settings');
                    generateNotifications();
                    showToast('All data cleared.');
                });
            });
        }

        // Restore saved profile
        const profile = JSON.parse(localStorage.getItem('structcrm_profile') || 'null');
        if (profile) {
            const nameInput = document.getElementById('settings-name');
            const roleInput = document.getElementById('settings-role');
            if (nameInput) nameInput.value = profile.name;
            if (roleInput) roleInput.value = profile.role;
        }
    }

    // Toast notification
    function showToast(message) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; background: var(--text-main);
            color: var(--bg-surface); padding: 12px 24px; border-radius: var(--radius-md);
            font-family: var(--font-family); font-size: 0.9rem; font-weight: 500;
            box-shadow: var(--shadow-float); z-index: 300; animation: fadeSlideUp 0.3s ease;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    // =============================================
    // SEARCH
    // =============================================
    let searchDebounceTimer = null;
    el.searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => performSearch(el.searchInput.value.trim()), 300);
    });

    el.searchInput.addEventListener('focus', () => {
        if (el.searchInput.value.trim()) performSearch(el.searchInput.value.trim());
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-wrapper')) {
            el.searchResults.classList.remove('visible');
        }
    });

    function performSearch(query) {
        if (!query || query.length < 2) {
            el.searchResults.classList.remove('visible');
            return;
        }

        const q = query.toLowerCase();
        const results = [];

        const searchEntities = [
            { entity: 'leads', icon: 'users', color: 'var(--accent)', bgColor: 'var(--accent-soft)', label: 'Leads', nameField: 'title', subField: 'client', viewId: 'leads' },
            { entity: 'projects', icon: 'briefcase', color: 'var(--success)', bgColor: 'var(--success-soft)', label: 'Projects', nameField: 'name', subField: 'client', viewId: 'projects' },
            { entity: 'tasks', icon: 'check-square', color: 'var(--warning)', bgColor: 'var(--warning-soft)', label: 'Tasks', nameField: 'title', subField: 'project', viewId: 'tasks' },
            { entity: 'invoices', icon: 'receipt', color: 'var(--purple)', bgColor: 'var(--purple-soft)', label: 'Invoices', nameField: 'invoiceNumber', subField: 'project', viewId: 'invoices' },
            { entity: 'changeOrders', icon: 'file-diff', color: 'var(--danger)', bgColor: 'var(--danger-soft)', label: 'Change Orders', nameField: 'coNumber', subField: 'desc', viewId: 'change-orders' }
        ];

        searchEntities.forEach(config => {
            const items = store.getAll(config.entity).filter(item => {
                const name = (item[config.nameField] || '').toLowerCase();
                const sub = (item[config.subField] || '').toLowerCase();
                const client = (item.client || '').toLowerCase();
                return name.includes(q) || sub.includes(q) || client.includes(q);
            });
            if (items.length > 0) {
                results.push({ ...config, items: items.slice(0, 3) });
            }
        });

        if (results.length === 0) {
            el.searchResults.innerHTML = '<div class="search-results-empty">No results for "' + query + '"</div>';
        } else {
            el.searchResults.innerHTML = results.map(group => `
                <div class="search-result-group">
                    <div class="search-result-group-title">${group.label}</div>
                    ${group.items.map(item => `
                        <div class="search-result-item" data-view="${group.viewId}">
                            <div class="search-result-icon" style="background:${group.bgColor};color:${group.color};">
                                <i data-lucide="${group.icon}"></i>
                            </div>
                            <div class="search-result-text">
                                <strong>${item[group.nameField]}</strong>
                                <span>${item[group.subField] || ''}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `).join('');
        }

        lucide.createIcons({ nodes: [el.searchResults] });
        el.searchResults.classList.add('visible');

        el.searchResults.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', () => {
                const viewId = item.dataset.view;
                el.searchResults.classList.remove('visible');
                el.searchInput.value = '';
                navigateToView(viewId);
            });
        });
    }

    function navigateToView(viewId) {
        el.navItems.forEach(nav => {
            nav.classList.remove('active');
            if (nav.dataset.view === viewId) {
                nav.classList.add('active');
                el.viewTitle.textContent = nav.textContent.trim();
            }
        });
        loadView(viewId);
    }

    // =============================================
    // NOTIFICATIONS
    // =============================================
    let notificationPanelOpen = false;

    el.notificationBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notificationPanelOpen = !notificationPanelOpen;
        el.notificationPanel.classList.toggle('visible', notificationPanelOpen);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.notification-panel') && !e.target.closest('.notification-btn')) {
            notificationPanelOpen = false;
            el.notificationPanel.classList.remove('visible');
        }
    });

    el.markAllReadBtn.addEventListener('click', () => {
        const data = store._getData();
        data.notifications = data.notifications.map(n => ({ ...n, read: true }));
        store._setData(data);
        renderNotifications();
    });

    function generateNotifications() {
        const notifications = [];
        const tasks = store.getAll('tasks');
        const invoices = store.getAll('invoices');
        const changeOrders = store.getAll('changeOrders');

        // Overdue invoices
        invoices.filter(i => i.status === 'Overdue').forEach(inv => {
            notifications.push({
                id: 'n-inv-' + inv.id,
                icon: 'alert-circle',
                iconBg: 'var(--danger-soft)',
                iconColor: 'var(--danger)',
                text: `Invoice ${inv.invoiceNumber} is overdue`,
                time: formatDate(inv.date),
                read: false
            });
        });

        // Pending change orders
        changeOrders.filter(c => c.status === 'Pending Approval').forEach(co => {
            notifications.push({
                id: 'n-co-' + co.id,
                icon: 'file-diff',
                iconBg: 'var(--warning-soft)',
                iconColor: 'var(--warning)',
                text: `${co.coNumber} awaiting approval`,
                time: co.project,
                read: false
            });
        });

        // Tasks due soon
        tasks.filter(t => !t.completed).slice(0, 2).forEach(task => {
            notifications.push({
                id: 'n-task-' + task.id,
                icon: 'check-square',
                iconBg: 'var(--accent-soft)',
                iconColor: 'var(--accent)',
                text: `Task due: ${task.title}`,
                time: formatDate(task.due),
                read: false
            });
        });

        // Preserve read state from existing notifications
        const existing = store.getAll('notifications');
        notifications.forEach(n => {
            const old = existing.find(e => e.id === n.id);
            if (old) n.read = old.read;
        });

        const data = store._getData();
        data.notifications = notifications;
        store._setData(data);

        renderNotifications();
    }

    function renderNotifications() {
        const notifications = store.getAll('notifications');
        const unreadCount = notifications.filter(n => !n.read).length;

        el.notificationBadge.textContent = unreadCount;
        el.notificationBadge.style.display = unreadCount > 0 ? '' : 'none';

        if (notifications.length === 0) {
            el.notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
            return;
        }

        el.notificationList.innerHTML = notifications.map(n => `
            <div class="notification-item ${n.read ? '' : 'unread'}" data-id="${n.id}">
                <div class="notification-item-icon" style="background:${n.iconBg};color:${n.iconColor};">
                    <i data-lucide="${n.icon}"></i>
                </div>
                <div class="notification-item-content">
                    <p>${n.text}</p>
                    <span>${n.time}</span>
                </div>
            </div>
        `).join('');

        lucide.createIcons({ nodes: [el.notificationList] });

        el.notificationList.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => {
                const data = store._getData();
                const n = data.notifications.find(n => n.id === item.dataset.id);
                if (n) n.read = true;
                store._setData(data);
                renderNotifications();
            });
        });
    }

    // =============================================
    // UTILITIES
    // =============================================
    function formatDate(dateStr) {
        if (!dateStr) return '—';
        try {
            const d = new Date(dateStr + (dateStr.includes('T') ? '' : 'T00:00:00'));
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch {
            return dateStr;
        }
    }

    // =============================================
    // INIT
    // =============================================
    // Restore profile
    const savedProfile = JSON.parse(localStorage.getItem('structcrm_profile') || 'null');
    if (savedProfile) {
        const sidebarName = document.querySelector('.user-name');
        const sidebarRole = document.querySelector('.user-role');
        if (sidebarName) sidebarName.textContent = savedProfile.name;
        if (sidebarRole) sidebarRole.textContent = savedProfile.role;
    }

    generateNotifications();
    loadView('dashboard');
});
