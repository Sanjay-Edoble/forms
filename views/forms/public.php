<?php \App\Core\View::extend('layouts.public'); ?>
<?php \App\Core\View::section('head'); ?>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<style>
    .form-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 32px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }
    .form-header {
        border-top: 8px solid var(--form-primary);
    }
    .form-title { font-size: 32px; font-weight: 700; margin: 0 0 12px 0; color: #0f172a; }
    .form-desc { font-size: 15px; color: #475569; line-height: 1.6; white-space: pre-wrap; }
    
    .q-title { font-size: 16px; font-weight: 600; margin: 0 0 16px 0; color: #1e293b; }
    .q-title .required { color: #ef4444; margin-left: 4px; }
    
    .edf-input {
        width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px;
        font-family: inherit; font-size: 15px; transition: border 0.2s;
        box-sizing: border-box;
    }
    .edf-input:focus { outline: none; border-color: var(--form-primary); }
    
    .option-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .option-row input[type="radio"], .option-row input[type="checkbox"] {
        width: 18px; height: 18px; accent-color: var(--form-primary); cursor: pointer;
    }
    .option-row label { cursor: pointer; font-size: 15px; color: #334155; }
    
    .scale-row { display: flex; align-items: center; justify-content: space-between; max-width: 500px; margin: 0 auto; }
    .scale-item { display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .scale-label { font-size: 13px; color: #64748b; }
    
    .rating-row { display: flex; gap: 8px; font-size: 28px; color: #cbd5e1; cursor: pointer; }
    .rating-row .star:hover, .rating-row .star.active { color: #f59e0b; }
    
    .submit-btn {
        background: var(--form-primary); color: #fff; border: none; border-radius: 6px;
        padding: 12px 24px; font-size: 16px; font-weight: 600; cursor: pointer; font-family: inherit;
        transition: opacity 0.2s;
    }
    .submit-btn:hover { opacity: 0.9; }
    
    .validation-error { color: #ef4444; font-size: 13px; margin-top: 8px; display: flex; align-items: center; gap: 4px; }
    
    .fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.4s ease; }
    .fade-slide-enter-from { opacity: 0; transform: translateY(20px); }
    .fade-slide-leave-to { opacity: 0; transform: translateY(-20px); position: absolute; width: 100%; }
</style>
<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('content'); ?>
<?php if (get_flash('form_submitted')): ?>
    <div class="form-card form-header">
        <h1 class="form-title"><?= e($form['title']) ?></h1>
        <div class="form-desc mt-4" style="font-size:16px;">
            <?= e(get_flash('confirmation_message') ?? 'Your response has been recorded.') ?>
        </div>
        <?php if ($settings['allow_multiple'] ?? true): ?>
            <div class="mt-4 pt-4" style="border-top:1px solid #e2e8f0;">
                <a href="/f/<?= $form['id'] ?>" style="color:var(--form-primary);text-decoration:none;font-weight:500;">Submit another response</a>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>

<div id="form-app" v-cloak>
    <!-- Success End Card -->
    <div v-if="isSubmitted" class="form-card form-header" style="text-align: center; padding: 60px 30px;">
        <h1 class="form-title"><?= e($form['title']) ?></h1>
        <div class="form-desc" style="font-size: 18px; margin-top: 16px; margin-bottom: 24px; color: #334155;">
            {{ confirmationMessage }}
        </div>
        
        <?php if (empty($settings['limit_one_response'])): ?>
            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
                <a href="javascript:void(0)" @click="resetForm" style="color: var(--form-primary); text-decoration: none; font-weight: 500; font-size: 15px;">Submit another response</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Form Interface -->
    <form @submit.prevent="submitForm" v-else>
        <template v-if="formSections[currentSectionIndex]">
            <div class="form-card form-header" v-if="currentSectionIndex === 0">
                <h1 class="form-title">{{ formTitle }}</h1>
                <div class="form-desc" v-if="description">{{ description }}</div>
                <div class="validation-error" style="margin-top: 16px; padding: 12px; background: #fee2e2; border-radius: 6px;" v-if="serverErrors._general">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ serverErrors._general }}
                </div>
            </div>
            <div class="form-card form-header" v-else>
                <h2 class="form-title">{{ formSections[currentSectionIndex].title || 'Untitled Section' }}</h2>
                <div class="form-desc" v-if="formSections[currentSectionIndex].description">{{ formSections[currentSectionIndex].description }}</div>
            </div>

            <div v-if="currentSectionIndex === 0 && collectEmail" class="form-card" v-show="!presentationMode || currentPresentationIndex === -1">
                <h3 class="q-title">Email address <span class="required">*</span></h3>
                <div v-if="verifiedEmail" style="padding: 12px 16px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; gap: 8px; color: #475569; font-size: 15px;">
                    <i class="bi bi-check-circle-fill" style="color: #10b981;"></i>
                    Your response will be recorded as <strong>{{ verifiedEmail }}</strong>
                </div>
                <div v-else>
                    <input type="email" v-model="answers._email" class="edf-input" required placeholder="Your email">
                    <div class="validation-error" v-if="serverErrors._email">{{ serverErrors._email }}</div>
                </div>
            </div>

            <transition-group name="fade-slide" tag="div" style="position: relative;">
                <div v-for="(q, index) in formSections[currentSectionIndex].questions" :key="q.id" class="form-card" :class="{'has-error': serverErrors[q.id]}" v-show="!presentationMode || currentPresentationIndex === index">
                    <h3 class="q-title">{{ q.title || 'Untitled Question' }} <span v-if="q.required" class="required">*</span></h3>
                    
                    <template v-if="q.type === 'short_text'">
                        <input type="text" v-model="answers[q.id]" class="edf-input" :required="q.required" placeholder="Your answer" @keydown.enter.prevent="presentationMode ? nextQuestion() : null">
                    </template>
                    
                    <template v-else-if="q.type === 'paragraph'">
                        <textarea v-model="answers[q.id]" class="edf-input" rows="4" :required="q.required" placeholder="Your answer"></textarea>
                    </template>
                    
                    <template v-else-if="q.type === 'email'">
                        <input type="email" v-model="answers[q.id]" class="edf-input" :required="q.required" placeholder="Your email" @keydown.enter.prevent="presentationMode ? nextQuestion() : null">
                    </template>
                    
                    <template v-else-if="q.type === 'phone'">
                        <input type="tel" v-model="answers[q.id]" class="edf-input" :required="q.required" placeholder="Your phone number" @keydown.enter.prevent="presentationMode ? nextQuestion() : null">
                    </template>
                    
                    <template v-else-if="q.type === 'date'">
                        <input type="date" v-model="answers[q.id]" class="edf-input" :required="q.required" style="max-width: 200px;">
                    </template>
                    
                    <template v-else-if="q.type === 'time'">
                        <input type="time" v-model="answers[q.id]" class="edf-input" :required="q.required" style="max-width: 150px;">
                    </template>
                    
                    <template v-else-if="q.type === 'multiple_choice'">
                        <div class="option-row" v-for="opt in q.options" :key="opt.value">
                            <input type="radio" :name="q.id" :value="opt.value" :id="q.id+'_'+opt.value" v-model="answers[q.id]" :required="q.required">
                            <label :for="q.id+'_'+opt.value">{{ opt.value }}</label>
                        </div>
                    </template>
                    
                    <template v-else-if="q.type === 'checkboxes'">
                        <div class="option-row" v-for="opt in q.options" :key="opt.value">
                            <input type="checkbox" :value="opt.value" :id="q.id+'_'+opt.value" v-model="answers[q.id]">
                            <label :for="q.id+'_'+opt.value">{{ opt.value }}</label>
                        </div>
                    </template>
                    
                    <template v-else-if="q.type === 'dropdown'">
                        <select v-model="answers[q.id]" class="edf-input" :required="q.required">
                            <option value="" disabled>Choose</option>
                            <option v-for="opt in q.options" :key="opt.value" :value="opt.value">{{ opt.value }}</option>
                        </select>
                    </template>
                    
                    <template v-else-if="q.type === 'linear_scale'">
                        <div class="scale-row">
                            <span class="scale-label">{{ q.minLabel }}</span>
                            <div class="scale-item" v-for="n in getScaleRange(q.scaleMin, q.scaleMax)" :key="n">
                                <label :for="q.id+'_'+n">{{ n }}</label>
                                <input type="radio" :name="q.id" :value="n" :id="q.id+'_'+n" v-model="answers[q.id]" :required="q.required">
                            </div>
                            <span class="scale-label">{{ q.maxLabel }}</span>
                        </div>
                    </template>
                    
                    <template v-else-if="q.type === 'rating'">
                        <div class="rating-row">
                            <i class="bi star" v-for="n in (q.max || 5)" :key="n" :class="(answers[q.id] >= n) ? 'bi-star-fill active' : 'bi-star'" @click="answers[q.id] = n"></i>
                        </div>
                        <input type="hidden" v-model="answers[q.id]" :required="q.required">
                    </template>
                    
                    <div class="validation-error" v-if="serverErrors[q.id]"><i class="bi bi-exclamation-circle"></i> {{ serverErrors[q.id] }}</div>
                </div>
            </transition-group>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px;">
                <div class="d-flex gap-2">
                    <button type="button" class="edf-btn edf-btn-secondary" v-if="showBackButton" @click="prevQuestionOrSection">Back</button>
                    <button type="button" class="submit-btn" v-if="!isLastQuestionOrSection && !nextIsSubmit" @click="nextQuestionOrSection">Next</button>
                    <button type="submit" class="submit-btn" :disabled="isSubmitting" v-else>
                        {{ isSubmitting ? 'Submitting...' : 'Submit' }}
                    </button>
                </div>
                <span style="font-size:13px; color:#64748b;">Never submit passwords through forms.</span>
            </div>
        </template>
    </form>
</div>
<?php endif; ?>
<?php \App\Core\View::endSection(); ?>

<?php \App\Core\View::section('scripts'); ?>
<script>
const schema = <?= json_encode($schema) ?>;
const settings = <?= json_encode($settings) ?>;
const formId = "<?= e($form['id']) ?>";
const initialErrors = <?= json_encode(get_flash('validation_errors') ?? new stdClass()) ?>;
const verifiedEmail = <?= json_encode($verifiedEmail ?? null) ?>;

const app = Vue.createApp({
    data() {
        // Initialize answers
        const answers = {};
        if (settings.collect_email) answers._email = '';
        (schema.questions || []).forEach(q => {
            answers[q.id] = (q.type === 'checkboxes') ? [] : '';
        });
        
        const formTitle = <?= json_encode($form['title']) ?>;
        const description = <?= json_encode($form['description']) ?>;
        let formSections = [];
        let currentSection = { id: 'default', title: formTitle, description: description, questions: [] };
        
        (schema.questions || []).forEach(q => {
            if (q.type === 'section') {
                formSections.push(currentSection);
                currentSection = { id: q.id, title: q.title, description: q.description, questions: [], jumpTo: q.jumpTo || null };
            } else {
                currentSection.questions.push(q);
                answers[q.id] = (q.type === 'checkboxes') ? [] : '';
            }
        });
        formSections.push(currentSection);
        
        return {
            formTitle: formTitle,
            description: description,
            confirmationMessage: settings.confirmation_message || 'Your response has been recorded.',
            formSections: formSections,
            currentSectionIndex: 0,
            sectionHistory: [],
            nextIsSubmit: false,
            collectEmail: settings.collect_email || false,
            verifiedEmail: verifiedEmail,
            answers: answers,
            serverErrors: initialErrors,
            isSubmitting: false,
            isSubmitted: false,
            presentationMode: settings.presentation_mode || false,
            currentPresentationIndex: (settings.collect_email && !verifiedEmail) ? -1 : 0
        }
    },
    computed: {
        showBackButton() {
            if (this.presentationMode) {
                return this.currentPresentationIndex > ((this.collectEmail && !this.verifiedEmail && this.currentSectionIndex === 0) ? -1 : 0) || this.sectionHistory.length > 0;
            }
            return this.sectionHistory.length > 0;
        },
        isLastQuestionOrSection() {
            if (this.presentationMode) {
                return this.currentSectionIndex === this.formSections.length - 1 && this.currentPresentationIndex >= this.formSections[this.currentSectionIndex].questions.length - 1;
            }
            return this.currentSectionIndex >= this.formSections.length - 1;
        }
    },
    methods: {
        getScaleRange(min, max) {
            const arr = [];
            for (let i = (min || 1); i <= (max || 5); i++) arr.push(i);
            return arr;
        },
        handleCheckbox(qId) {
            // Vue v-model array binding handles the rest
        },
        resetForm() {
            // Reset answers
            if (this.collectEmail) this.answers._email = '';
            this.formSections.forEach(sec => {
                sec.questions.forEach(q => {
                    this.answers[q.id] = (q.type === 'checkboxes') ? [] : '';
                });
            });
            this.serverErrors = {};
            this.isSubmitted = false;
            this.currentSectionIndex = 0;
            this.currentPresentationIndex = (this.collectEmail && !this.verifiedEmail) ? -1 : 0;
            this.sectionHistory = [];
            this.nextIsSubmit = false;
            window.scrollTo(0, 0);
        },
        nextQuestionOrSection() {
            if (this.presentationMode) {
                this.nextQuestion();
            } else {
                this.nextSection();
            }
        },
        prevQuestionOrSection() {
            if (this.presentationMode) {
                this.prevQuestion();
            } else {
                this.prevSection();
            }
        },
        nextQuestion() {
            let hasError = false;
            
            // Validate current question only
            if (this.currentPresentationIndex === -1 && this.collectEmail) {
                if (!this.answers._email) {
                    this.serverErrors._email = 'This field is required';
                    hasError = true;
                } else {
                    delete this.serverErrors._email;
                }
            } else if (this.currentPresentationIndex >= 0) {
                const q = this.formSections[this.currentSectionIndex].questions[this.currentPresentationIndex];
                if (q && q.required && (!this.answers[q.id] || this.answers[q.id].length === 0)) {
                    this.serverErrors[q.id] = 'This field is required';
                    hasError = true;
                } else if (q) {
                    delete this.serverErrors[q.id];
                }
            }
            
            if (hasError) return;

            // Logic jumps for single question
            let jumpTo = null;
            if (this.currentPresentationIndex >= 0) {
                const q = this.formSections[this.currentSectionIndex].questions[this.currentPresentationIndex];
                if (q && q.logic_enabled && ['multiple_choice', 'dropdown'].includes(q.type)) {
                    const answer = this.answers[q.id];
                    const option = (q.options || []).find(o => o.value === answer);
                    if (option && option.jumpTo) jumpTo = option.jumpTo;
                }
            }

            if (jumpTo === 'submit') {
                this.nextIsSubmit = true;
                this.submitForm(new Event('submit'));
                return;
            }

            if (jumpTo) {
                const idx = this.formSections.findIndex(s => s.id === jumpTo);
                if (idx !== -1) {
                    this.sectionHistory.push({s: this.currentSectionIndex, q: this.currentPresentationIndex});
                    this.currentSectionIndex = idx;
                    this.currentPresentationIndex = 0;
                    return;
                }
            }

            // Move to next question or section
            if (this.currentPresentationIndex < this.formSections[this.currentSectionIndex].questions.length - 1) {
                this.sectionHistory.push({s: this.currentSectionIndex, q: this.currentPresentationIndex});
                this.currentPresentationIndex++;
            } else {
                // End of section, move to next section via section-level logic jump
                if (this.formSections[this.currentSectionIndex].jumpTo) {
                    jumpTo = this.formSections[this.currentSectionIndex].jumpTo;
                }
                
                if (jumpTo === 'submit') {
                    this.nextIsSubmit = true;
                    this.submitForm(new Event('submit'));
                    return;
                }
                
                if (jumpTo) {
                    const idx = this.formSections.findIndex(s => s.id === jumpTo);
                    if (idx !== -1) {
                        this.sectionHistory.push({s: this.currentSectionIndex, q: this.currentPresentationIndex});
                        this.currentSectionIndex = idx;
                        this.currentPresentationIndex = 0;
                        return;
                    }
                }

                if (this.currentSectionIndex < this.formSections.length - 1) {
                    this.sectionHistory.push({s: this.currentSectionIndex, q: this.currentPresentationIndex});
                    this.currentSectionIndex++;
                    this.currentPresentationIndex = 0;
                }
            }
        },
        prevQuestion() {
            if (this.sectionHistory.length > 0) {
                const prev = this.sectionHistory.pop();
                this.currentSectionIndex = prev.s;
                this.currentPresentationIndex = prev.q;
                this.nextIsSubmit = false;
            }
        },
        nextSection() {
            let hasError = false;
            const currentQuestions = this.formSections[this.currentSectionIndex].questions;
            for (let q of currentQuestions) {
                if (q.required && (!this.answers[q.id] || this.answers[q.id].length === 0)) {
                    this.serverErrors[q.id] = 'This field is required';
                    hasError = true;
                } else {
                    delete this.serverErrors[q.id];
                }
            }
            if (this.currentSectionIndex === 0 && this.collectEmail && !this.answers._email && !this.verifiedEmail) {
                this.serverErrors._email = 'This field is required';
                hasError = true;
            } else {
                delete this.serverErrors._email;
            }
            if (hasError) return;
            
            // Logic jumps
            let jumpTo = null;
            for (let q of currentQuestions) {
                if (q.logic_enabled && ['multiple_choice', 'dropdown'].includes(q.type)) {
                    const answer = this.answers[q.id];
                    const option = (q.options || []).find(o => o.value === answer);
                    if (option && option.jumpTo) {
                        jumpTo = option.jumpTo;
                        break;
                    }
                }
            }
            
            // If no question-level logic jump triggered, check the section-level jump
            if (!jumpTo && this.formSections[this.currentSectionIndex].jumpTo) {
                jumpTo = this.formSections[this.currentSectionIndex].jumpTo;
            }
            
            this.sectionHistory.push(this.currentSectionIndex);
            
            if (jumpTo === 'submit') {
                this.nextIsSubmit = true;
                this.submitForm(new Event('submit'));
                return;
            }
            
            if (jumpTo) {
                const idx = this.formSections.findIndex(s => s.id === jumpTo);
                if (idx !== -1) {
                    this.currentSectionIndex = idx;
                    window.scrollTo(0,0);
                    return;
                }
            }
            
            this.currentSectionIndex++;
            window.scrollTo(0,0);
        },
        prevSection() {
            if (this.sectionHistory.length > 0) {
                this.currentSectionIndex = this.sectionHistory.pop();
                this.nextIsSubmit = false;
                window.scrollTo(0,0);
            }
        },
        submitForm(e) {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            
            // Allow native form submission to fallback if js fails, but we'll try fetch first
            const form = e.target;
            
            const payload = new FormData();
            payload.append('answers', JSON.stringify(this.answers));
            payload.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
            
            fetch(`/f/${formId}/submit`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: payload
            })
            .then(res => res.json())
            .then(data => {
                this.isSubmitting = false;
                if (data.success) {
                    this.isSubmitted = true;
                    window.scrollTo(0, 0);
                } else {
                    if (data.errors) this.serverErrors = data.errors;
                    else this.serverErrors = { _general: data.message };
                }
            })
            .catch(err => {
                this.isSubmitting = false;
                // Fallback to standard post
                const nativeForm = document.createElement('form');
                nativeForm.method = 'POST';
                nativeForm.action = `/f/${formId}/submit`;
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = 'csrf_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                
                const ans = document.createElement('input');
                ans.type = 'hidden';
                ans.name = 'answers';
                ans.value = JSON.stringify(this.answers);
                
                nativeForm.appendChild(csrf);
                nativeForm.appendChild(ans);
                document.body.appendChild(nativeForm);
                nativeForm.submit();
            });
        }
    }
});
app.mount('#form-app');
</script>
<?php \App\Core\View::endSection(); ?>
