<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('head'); ?>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<!-- Ag-Grid for Live Data Sheet -->
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css" rel="stylesheet">
<style>
    .edf-page-content { padding: 0 !important; max-width: none !important; height: calc(100vh - var(--edf-topbar-height)); display: flex; flex-direction: column; }
    .responses-toolbar { padding: 12px 24px; background: var(--edf-bg-card); border-bottom: 1px solid var(--edf-border); display: flex; justify-content: space-between; align-items: center; }
    #grid-wrapper { flex: 1; width: 100%; }
    .ag-theme-alpine { --ag-font-family: var(--edf-font); --ag-font-size: 13px; --ag-header-background-color: var(--edf-bg); --ag-odd-row-background-color: var(--edf-bg-hover); }
    [data-theme="dark"] .ag-theme-alpine { 
        --ag-background-color: var(--edf-bg-card); --ag-header-background-color: var(--edf-bg);
        --ag-border-color: var(--edf-border); --ag-row-border-color: var(--edf-border-light);
        --ag-odd-row-background-color: var(--edf-bg-hover); --ag-header-foreground-color: var(--edf-text-muted);
        --ag-data-color: var(--edf-text-secondary); --ag-control-panel-background-color: var(--edf-bg-card);
    }
    .live-indicator { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--edf-success); }
    .live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--edf-success); animation: pulse 2s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.7); } 70% { box-shadow: 0 0 0 6px rgba(16,185,129,0); } 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); } }
</style>
<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('content'); ?>
<div id="responses-app" style="height:100%; display:flex; flex-direction:column;" v-cloak>
    
    <div class="responses-toolbar">
        <div class="d-flex align-center gap-3">
            <a href="/forms/<?= $form['id'] ?>/edit" class="edf-btn edf-btn-ghost edf-btn-sm"><i class="bi bi-arrow-left"></i> Back to Editor</a>
            <div class="live-indicator" v-if="status === 'published'">
                <div class="live-dot"></div> Live Updates On
            </div>
            <div class="edf-badge edf-badge-neutral" v-else>Form Closed</div>
        </div>
        
        <div class="d-flex align-center gap-2">
            <span class="text-muted" style="font-size:13px;">{{ totalCount }} Responses</span>
            
            <a href="/forms/<?= $form['id'] ?>/analytics" class="edf-btn edf-btn-secondary edf-btn-sm"><i class="bi bi-pie-chart"></i> Analytics</a>
            
            <div class="edf-dropdown">
                <button class="edf-btn edf-btn-primary edf-btn-sm" data-dropdown>
                    <i class="bi bi-download"></i> Export <i class="bi bi-chevron-down" style="font-size:10px;"></i>
                </button>
                <div class="edf-dropdown-menu">
                    <a href="/forms/<?= $form['id'] ?>/export/excel" class="edf-dropdown-item"><i class="bi bi-file-earmark-excel"></i> Excel (.xlsx)</a>
                    <a href="/forms/<?= $form['id'] ?>/export/csv" class="edf-dropdown-item"><i class="bi bi-filetype-csv"></i> CSV</a>
                </div>
            </div>
            
            <button @click="deleteSelected" class="edf-btn edf-btn-danger edf-btn-sm" v-if="selectedCount > 0">
                <i class="bi bi-trash"></i> Delete ({{ selectedCount }})
            </button>
        </div>
    </div>

    <div id="grid-wrapper" class="ag-theme-alpine"></div>
</div>

<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('scripts'); ?>
<script>
const formId = "<?= $form['id'] ?>";
const schema = <?= json_encode($schema) ?>;

const app = Vue.createApp({
    data() {
        return {
            status: "<?= $form['status'] ?? 'draft' ?>",
            totalCount: <?= $form['response_count'] ?? 0 ?>,
            selectedCount: 0,
            gridOptions: null,
            gridApi: null,
            columnDefs: [],
            rowData: [],
            lastPollTime: new Date().toISOString()
        }
    },
    mounted() {
        this.buildColumns();
        this.initGrid();
        this.fetchData();
        
        if (this.status === 'published') {
            setInterval(this.pollNewResponses, 5000); // Poll every 5s
        }
    },
    methods: {
        buildColumns() {
            this.columnDefs = [
                { headerName: "ID", field: "_id", hide: true },
                { 
                    headerName: "Timestamp", field: "submitted_at", sortable: true, filter: 'agDateColumnFilter', width: 180,
                    valueFormatter: params => new Date(params.value).toLocaleString() 
                }
            ];
            
            const questions = schema.questions || [];
            questions.forEach(q => {
                if (q.type === 'section') return;
                
                this.columnDefs.push({
                    headerName: q.title,
                    field: `answers.${q.id}`,
                    sortable: true,
                    filter: true,
                    resizable: true,
                    width: Math.min(Math.max(q.title.length * 10, 150), 300),
                    valueFormatter: params => {
                        if (Array.isArray(params.value)) return params.value.join(', ');
                        return params.value;
                    }
                });
            });
            
            this.columnDefs.push({
                headerName: "Actions",
                field: "actions",
                pinned: "right",
                width: 100,
                cellRenderer: params => {
                    const id = params.data.id || params.data._id;
                    return `<a href="/forms/${formId}/responses/${id}" class="edf-btn-ghost edf-btn-sm" style="color:var(--edf-primary)">View</a>`;
                }
            });
        },
        initGrid() {
            this.gridOptions = {
                columnDefs: this.columnDefs,
                rowData: this.rowData,
                rowSelection: 'multiple',
                pagination: true,
                paginationPageSize: 50,
                onSelectionChanged: () => {
                    this.selectedCount = this.gridApi.getSelectedRows().length;
                }
            };
            
            const eGridDiv = document.querySelector('#grid-wrapper');
            this.gridApi = agGrid.createGrid(eGridDiv, this.gridOptions);
        },
        async fetchData() {
            try {
                // For a real app, this should ideally be an infinite scroll datasource with ag-Grid Server-Side Row Model.
                // For this implementation, we fetch all (paginated internally by Edobase or fetch up to a limit).
                const res = await Edoble.fetch(`/api/forms/${formId}/responses?limit=1000`);
                if (res.success) {
                    this.rowData = res.data.map(r => {
                        r.answers = JSON.parse(r.answers || '{}');
                        return r;
                    });
                    this.gridApi.setGridOption('rowData', this.rowData);
                    this.totalCount = this.rowData.length;
                    this.lastPollTime = new Date().toISOString();
                }
            } catch (e) {
                Edoble.toast('Failed to load responses', 'error');
            }
        },
        async pollNewResponses() {
            try {
                const res = await Edoble.fetch(`/api/forms/${formId}/responses/poll?since=${encodeURIComponent(this.lastPollTime)}`);
                if (res.success && res.new_count > 0) {
                    const newRows = res.data.map(r => {
                        r.answers = JSON.parse(r.answers || '{}');
                        return r;
                    });
                    
                    this.gridApi.applyTransaction({ add: newRows, addIndex: 0 });
                    this.totalCount += res.new_count;
                    this.lastPollTime = new Date().toISOString();
                    Edoble.toast(`${res.new_count} new response(s) received!`, 'info', 3000);
                }
            } catch (e) {
                // Silent fail on polling to not annoy user
            }
        },
        async deleteSelected() {
            const selectedRows = this.gridApi.getSelectedRows();
            if (selectedRows.length === 0) return;
            
            if (await Edoble.confirm(`Are you sure you want to delete ${selectedRows.length} response(s)? This cannot be undone.`)) {
                try {
                    const ids = selectedRows.map(r => r.id || r._id);
                    const res = await Edoble.fetch(`/forms/${formId}/responses/bulk-delete`, {
                        method: 'POST',
                        body: { ids: JSON.stringify(ids) }
                    });
                    
                    if (res.success) {
                        this.gridApi.applyTransaction({ remove: selectedRows });
                        this.totalCount -= selectedRows.length;
                        this.selectedCount = 0;
                        Edoble.toast('Responses deleted successfully', 'success');
                    }
                } catch (e) {
                    Edoble.toast('Failed to delete responses', 'error');
                }
            }
        }
    }
});
app.mount('#responses-app');
</script>
<?php \App\Core\View::endSection(); ?>
