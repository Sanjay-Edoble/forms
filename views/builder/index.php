<?php \App\Core\View::extend('layouts.app'); ?>
<?php \App\Core\View::section('head'); ?>
<!-- Vue.js for reactivity -->
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<!-- Sortable.js for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<link href="<?= asset('css/builder.css') ?>" rel="stylesheet">
<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('content'); ?>
<div id="builder-app" v-cloak>
    <!-- Builder Topbar Toolbar -->
    <div class="edf-builder-toolbar">
        <div class="edf-builder-tabs">
            <a href="/forms/<?= $form['id'] ?>/edit" class="active">Questions</a>
            <a href="/forms/<?= $form['id'] ?>/settings">Settings</a>
            <a href="/forms/<?= $form['id'] ?>/responses">Responses <span class="edf-badge edf-badge-neutral">{{ responseCount }}</span></a>
        </div>
        <div class="edf-builder-actions">
            <span class="save-status" :class="saveStatus">{{ saveMessage }}</span>
            <button @click="openThemePanel" class="edf-btn edf-btn-ghost edf-btn-icon" title="Customize Theme"><i class="bi bi-palette"></i></button>
            <a href="/forms/<?= $form['id'] ?>/preview" target="_blank" class="edf-btn edf-btn-ghost edf-btn-icon" title="Preview"><i class="bi bi-eye"></i></a>
            <button @click="publishForm" class="edf-btn edf-btn-primary" v-if="status !== 'published'"><i class="bi bi-rocket"></i> Publish</button>
            <button @click="unpublishForm" class="edf-btn edf-btn-secondary" v-if="status === 'published'"><i class="bi bi-pause-circle"></i> Unpublish</button>
            <a href="/forms/<?= $form['id'] ?>/share" class="edf-btn edf-btn-primary" v-if="status === 'published'"><i class="bi bi-share"></i> Share</a>
        </div>
    </div>

    <div class="edf-builder-workspace">
        <!-- Main Form Canvas -->
        <div class="edf-builder-canvas">
            <!-- Form Header (Title & Desc) -->
            <div class="edf-builder-card form-header-card" :style="{ borderTopColor: theme.primary_color }">
                <input type="text" v-model="title" class="form-title-input" placeholder="Form Title" @blur="saveMeta">
                <textarea v-model="description" class="form-desc-input" placeholder="Form Description (optional)" @blur="saveMeta"></textarea>
            </div>

            <!-- Questions List -->
            <div id="questions-list" class="questions-container">
                <div v-for="(question, index) in questions" :key="question.id" class="edf-builder-card question-card" :class="{ 'active': activeQuestion === question.id }" @click="activeQuestion = question.id">
                    
                    <div class="question-drag-handle"><i class="bi bi-grid-3x2-gap"></i></div>

                    <!-- Question View/Edit Mode -->
                    <div class="question-content">
                        <div class="d-flex gap-3 mb-3">
                            <input type="text" v-model="question.title" class="question-title-input flex-1" placeholder="Question" @change="debouncedSave">
                            
                            <select v-model="question.type" class="edf-input question-type-select" @change="handleTypeChange(question)">
                                <optgroup label="Text">
                                    <option value="short_text">Short Answer</option>
                                    <option value="paragraph">Paragraph</option>
                                </optgroup>
                                <optgroup label="Choices">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="checkboxes">Checkboxes</option>
                                    <option value="dropdown">Dropdown</option>
                                </optgroup>
                                <optgroup label="Scale & Rating">
                                    <option value="linear_scale">Linear Scale</option>
                                    <option value="rating">Rating (Stars)</option>
                                </optgroup>
                                <optgroup label="Other">
                                    <option value="date">Date</option>
                                    <option value="time">Time</option>
                                    <option value="phone">Phone Number</option>
                                    <option value="email">Email</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Question Type Specific UI -->
                        
                        <!-- Text / Email / Phone -->
                        <div v-if="['short_text', 'paragraph', 'email', 'phone'].includes(question.type)" class="question-preview-input">
                            {{ question.type === 'paragraph' ? 'Long answer text' : 'Short answer text' }}
                        </div>

                        <!-- Multiple Choice / Checkboxes / Dropdown -->
                        <div v-if="['multiple_choice', 'checkboxes', 'dropdown'].includes(question.type)" class="options-list">
                            <div v-for="(opt, oIdx) in question.options" :key="oIdx" class="option-item">
                                <i :class="getOptionIcon(question.type)" class="option-icon"></i>
                                <input type="text" v-model="opt.value" class="option-input" placeholder="Option" @change="debouncedSave" @keyup.enter="addOption(question)">
                                <button @click.stop="removeOption(question, oIdx)" class="remove-option-btn" v-if="question.options.length > 1"><i class="bi bi-x"></i></button>
                            </div>
                            <div class="option-item add-option" @click="addOption(question)">
                                <i :class="getOptionIcon(question.type)" class="option-icon"></i>
                                <span>Add option</span>
                            </div>
                        </div>

                        <!-- Linear Scale -->
                        <div v-if="question.type === 'linear_scale'" class="scale-editor">
                            <div class="d-flex align-center gap-2 mb-2">
                                <select v-model.number="question.scaleMin" class="edf-input" style="width:70px" @change="debouncedSave">
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                </select>
                                <span>to</span>
                                <select v-model.number="question.scaleMax" class="edf-input" style="width:70px" @change="debouncedSave">
                                    <option v-for="n in 10" :key="n" :value="n" v-show="n > 1">{{ n }}</option>
                                </select>
                            </div>
                            <div class="d-flex align-center gap-2 mt-2">
                                <span style="width:20px;text-align:right">{{ question.scaleMin }}</span>
                                <input type="text" v-model="question.minLabel" class="edf-input flex-1" placeholder="Label (optional)" @change="debouncedSave">
                            </div>
                            <div class="d-flex align-center gap-2 mt-2">
                                <span style="width:20px;text-align:right">{{ question.scaleMax }}</span>
                                <input type="text" v-model="question.maxLabel" class="edf-input flex-1" placeholder="Label (optional)" @change="debouncedSave">
                            </div>
                        </div>

                        <!-- Rating -->
                        <div v-if="question.type === 'rating'" class="rating-editor">
                            <div class="d-flex align-center gap-2">
                                <span>Levels:</span>
                                <select v-model.number="question.max" class="edf-input" style="width:80px" @change="debouncedSave">
                                    <option v-for="n in [3,4,5,10]" :key="n" :value="n">{{ n }}</option>
                                </select>
                                <span style="margin-left:12px;color:var(--edf-warning);font-size:20px;">
                                    <i class="bi bi-star-fill" v-for="n in Math.min(question.max, 5)"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Question Footer (Actions) -->
                    <div class="question-footer" v-show="activeQuestion === question.id">
                        <div class="footer-left">
                            <button @click="duplicateQuestion(index)" class="action-btn" title="Duplicate"><i class="bi bi-copy"></i></button>
                            <button @click="deleteQuestion(index)" class="action-btn" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="footer-right">
                            <label class="edf-toggle">
                                <input type="checkbox" v-model="question.required" @change="debouncedSave">
                                <div class="edf-toggle-track"></div>
                                <span class="edf-toggle-label">Required</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Action Menu (Side of active card) -->
            <div class="floating-actions" :class="{'visible': true}">
                <button @click="addQuestion" class="fab-btn" title="Add Question"><i class="bi bi-plus-circle"></i></button>
                <button class="fab-btn" title="Add Title/Description"><i class="bi bi-type"></i></button>
                <button class="fab-btn" title="Add Section"><i class="bi bi-view-stacked"></i></button>
            </div>
            
            <div style="height:100px;"></div>
        </div>
    </div>

    <!-- Theme Panel Sidebar -->
    <div class="theme-panel" :class="{ 'open': showThemePanel }">
        <div class="theme-panel-header">
            <h3>Theme Options</h3>
            <button @click="showThemePanel = false" class="edf-btn-ghost edf-btn-icon"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="theme-panel-body">
            <div class="edf-form-group">
                <label class="edf-label">Header Image</label>
                <div class="image-placeholder">
                    <i class="bi bi-image"></i> Choose Image
                </div>
            </div>
            <div class="edf-form-group">
                <label class="edf-label">Theme Color</label>
                <div class="color-picker">
                    <div v-for="c in ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#ef4444', '#0f172a']" 
                         class="color-swatch" :style="{backgroundColor: c}" 
                         :class="{'active': theme.primary_color === c}"
                         @click="theme.primary_color = c; saveTheme()"></div>
                </div>
            </div>
            <div class="edf-form-group mt-3">
                <label class="edf-label">Background Color</label>
                <div class="color-picker">
                    <div v-for="c in ['#f8f9fc', '#ffffff', '#fdf2f8', '#ecfdf5', '#fffbeb', '#eff6ff', '#f5f3ff', '#f1f5f9']" 
                         class="color-swatch" :style="{backgroundColor: c, border: '1px solid #e5e7eb'}" 
                         :class="{'active': theme.bg_color === c}"
                         @click="theme.bg_color = c; saveTheme()"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('scripts'); ?>
<script>
const formData = <?= json_encode($form) ?>;
const initialSchema = <?= json_encode($schema) ?>;
const initialTheme = <?= json_encode($theme) ?>;
const formId = formData.id || formData._id;

const app = Vue.createApp({
    data() {
        return {
            title: formData.title || 'Untitled Form',
            description: formData.description || '',
            status: formData.status || 'draft',
            responseCount: formData.response_count || 0,
            questions: initialSchema.questions || [],
            sections: initialSchema.sections || [{id: 'sec_'+Date.now(), title: 'Untitled Section'}],
            theme: Object.assign({ primary_color: '#6366f1', bg_color: '#f8f9fc', font: 'Inter' }, initialTheme),
            
            activeQuestion: null,
            saveStatus: 'saved', // saving, saved, error
            saveMessage: 'All changes saved',
            showThemePanel: false,
            
            sortableInst: null
        }
    },
    mounted() {
        if (this.questions.length === 0) {
            this.addQuestion();
        }
        if (this.questions.length > 0 && !this.activeQuestion) {
            this.activeQuestion = this.questions[0].id;
        }
        
        this.initSortable();
    },
    methods: {
        initSortable() {
            const el = document.getElementById('questions-list');
            this.sortableInst = new Sortable(el, {
                handle: '.question-drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: (evt) => {
                    const item = this.questions.splice(evt.oldIndex, 1)[0];
                    this.questions.splice(evt.newIndex, 0, item);
                    this.saveSchema();
                }
            });
        },
        generateId() {
            return 'q_' + Math.random().toString(36).substr(2, 9);
        },
        addQuestion() {
            const q = {
                id: this.generateId(),
                type: 'multiple_choice',
                title: '',
                required: false,
                options: [{ value: 'Option 1' }]
            };
            
            let idx = this.questions.length;
            if (this.activeQuestion) {
                const curIdx = this.questions.findIndex(x => x.id === this.activeQuestion);
                if (curIdx !== -1) idx = curIdx + 1;
            }
            
            this.questions.splice(idx, 0, q);
            this.activeQuestion = q.id;
            this.saveSchema();
            
            setTimeout(() => {
                const el = document.querySelector('.question-card.active');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 50);
        },
        duplicateQuestion(index) {
            const q = JSON.parse(JSON.stringify(this.questions[index]));
            q.id = this.generateId();
            this.questions.splice(index + 1, 0, q);
            this.activeQuestion = q.id;
            this.saveSchema();
        },
        deleteQuestion(index) {
            this.questions.splice(index, 1);
            if (this.questions.length > 0) {
                this.activeQuestion = this.questions[Math.min(index, this.questions.length - 1)].id;
            } else {
                this.activeQuestion = null;
            }
            this.saveSchema();
        },
        handleTypeChange(q) {
            if (['multiple_choice', 'checkboxes', 'dropdown'].includes(q.type) && (!q.options || q.options.length === 0)) {
                q.options = [{ value: 'Option 1' }];
            }
            if (q.type === 'linear_scale') {
                q.scaleMin = q.scaleMin ?? 1;
                q.scaleMax = q.scaleMax ?? 5;
            }
            if (q.type === 'rating') {
                q.max = q.max ?? 5;
            }
            this.saveSchema();
        },
        addOption(q) {
            if (!q.options) q.options = [];
            q.options.push({ value: `Option ${q.options.length + 1}` });
            this.saveSchema();
        },
        removeOption(q, idx) {
            q.options.splice(idx, 1);
            this.saveSchema();
        },
        getOptionIcon(type) {
            if (type === 'multiple_choice') return 'bi bi-circle';
            if (type === 'checkboxes') return 'bi bi-square';
            return 'bi bi-list-ol';
        },
        
        openThemePanel() {
            this.showThemePanel = true;
        },
        
        /* Auto Save Methods */
        debouncedSave: Edoble.debounce(function() {
            this.saveSchema();
        }, 800),
        
        async saveMeta() {
            try {
                this.saveStatus = 'saving';
                this.saveMessage = 'Saving...';
                await Edoble.fetch(`/api/forms/${formId}/save-meta`, {
                    method: 'POST',
                    body: { title: this.title, description: this.description }
                });
                this.saveStatus = 'saved';
                this.saveMessage = 'All changes saved';
            } catch (e) {
                this.saveStatus = 'error';
                this.saveMessage = 'Failed to save';
                Edoble.toast('Failed to save title', 'error');
            }
        },
        
        async saveSchema() {
            try {
                this.saveStatus = 'saving';
                this.saveMessage = 'Saving...';
                await Edoble.fetch(`/api/forms/${formId}/save`, {
                    method: 'POST',
                    body: { questions: this.questions, sections: this.sections }
                });
                this.saveStatus = 'saved';
                this.saveMessage = 'All changes saved';
            } catch (e) {
                this.saveStatus = 'error';
                this.saveMessage = 'Failed to save';
                Edoble.toast('Failed to save changes', 'error');
            }
        },
        
        async saveTheme() {
            try {
                await Edoble.fetch(`/api/forms/${formId}/save-theme`, {
                    method: 'POST',
                    body: this.theme
                });
            } catch (e) {
                Edoble.toast('Failed to save theme', 'error');
            }
        },
        
        async publishForm() {
            try {
                const res = await Edoble.fetch(`/api/forms/${formId}/publish`, { method: 'POST' });
                if (res.success) {
                    this.status = 'published';
                    Edoble.toast('Form published successfully!', 'success');
                }
            } catch (e) {
                Edoble.toast('Failed to publish', 'error');
            }
        },
        
        async unpublishForm() {
            try {
                const res = await Edoble.fetch(`/api/forms/${formId}/unpublish`, { method: 'POST' });
                if (res.success) {
                    this.status = 'draft';
                    Edoble.toast('Form unpublished.', 'info');
                }
            } catch (e) {
                Edoble.toast('Failed to unpublish', 'error');
            }
        }
    }
});
app.mount('#builder-app');
</script>
<?php \App\Core\View::endSection(); ?>
