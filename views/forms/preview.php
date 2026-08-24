<?php \App\Core\View::extend('layouts.public'); ?>
<?php \App\Core\View::section('head'); ?>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<style>
    .form-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 32px; margin-bottom: 24px; border: 1px solid #e2e8f0; }
    .form-header { border-top: 8px solid var(--form-primary); }
    .form-title { font-size: 32px; font-weight: 700; margin: 0 0 12px 0; color: #0f172a; }
    .form-desc { font-size: 15px; color: #475569; line-height: 1.6; white-space: pre-wrap; }
    .q-title { font-size: 16px; font-weight: 600; margin: 0 0 16px 0; color: #1e293b; }
    .q-title .required { color: #ef4444; margin-left: 4px; }
    .edf-input { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 15px; transition: border 0.2s; box-sizing: border-box; }
    .edf-input:focus { outline: none; border-color: var(--form-primary); }
    .option-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .option-row input[type="radio"], .option-row input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--form-primary); cursor: pointer; }
    .option-row label { cursor: pointer; font-size: 15px; color: #334155; }
    .scale-row { display: flex; align-items: center; justify-content: space-between; max-width: 500px; margin: 0 auto; }
    .scale-item { display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .scale-label { font-size: 13px; color: #64748b; }
    .rating-row { display: flex; gap: 8px; font-size: 28px; color: #cbd5e1; cursor: pointer; }
    .rating-row .star:hover, .rating-row .star.active { color: #f59e0b; }
    .submit-btn { background: var(--form-primary); color: #fff; border: none; border-radius: 6px; padding: 12px 24px; font-size: 16px; font-weight: 600; cursor: pointer; font-family: inherit; transition: opacity 0.2s; }
    .submit-btn:hover { opacity: 0.9; }
</style>
<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('content'); ?>
<div style="background: #e0e7ff; color: #3730a3; padding: 16px; border-radius: 8px; margin-bottom: 24px; text-align: center; font-weight: 500;">
    <i class="bi bi-eye"></i> This is a preview. Responses will not be saved.
</div>

<div id="form-app">
    <div class="form-card form-header">
        <h1 class="form-title"><?= e($form['title']) ?></h1>
        <div class="form-desc"><?= e($form['description']) ?></div>
    </div>
    
    <div v-for="q in questions" :key="q.id" class="form-card">
        <h3 class="q-title">{{ q.title || 'Untitled Question' }} <span v-if="q.required" class="required">*</span></h3>
        
        <template v-if="q.type === 'short_text'">
            <input type="text" class="edf-input" :required="q.required" placeholder="Your answer">
        </template>
        <template v-else-if="q.type === 'paragraph'">
            <textarea class="edf-input" rows="4" :required="q.required" placeholder="Your answer"></textarea>
        </template>
        <template v-else-if="q.type === 'multiple_choice'">
            <div class="option-row" v-for="opt in q.options">
                <input type="radio" :name="q.id" :value="opt.value" :required="q.required">
                <label>{{ opt.value }}</label>
            </div>
        </template>
        <template v-else-if="q.type === 'checkboxes'">
            <div class="option-row" v-for="opt in q.options">
                <input type="checkbox" :value="opt.value">
                <label>{{ opt.value }}</label>
            </div>
        </template>
        <template v-else-if="q.type === 'dropdown'">
            <select class="edf-input" :required="q.required">
                <option value="" disabled selected>Choose</option>
                <option v-for="opt in q.options" :value="opt.value">{{ opt.value }}</option>
            </select>
        </template>
        <!-- Add more previews as needed -->
    </div>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <button type="button" class="submit-btn" onclick="alert('This is a preview. The form cannot be submitted.')">Submit</button>
    </div>
</div>

<script>
const schema = <?= json_encode($schema) ?>;
const app = Vue.createApp({
    data() { return { questions: schema.questions || [] } }
});
app.mount('#form-app');
</script>
<?php \App\Core\View::endSection(); ?>
