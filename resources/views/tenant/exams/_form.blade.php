{{--
    Shared form fields for Add / Edit exam modals.
    Expects Alpine `form` object and `terms` array in scope.
--}}

{{-- Name --}}
<div class="flex flex-col gap-1.5">
    <label class="block text-sm font-medium text-text-dark mb-1.5">Exam Name <span class="text-error">*</span></label>
    <input type="text" name="name" x-model="form.name" required
           placeholder="e.g. Mid-Term Exam, End of Term 1"
           class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
</div>

{{-- Term --}}
<div class="flex flex-col gap-1.5">
    <label class="block text-sm font-medium text-text-dark mb-1.5">Term <span class="text-error">*</span></label>
    <select name="term_id" x-model="form.term_id" required
            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
        <option value="">Select a term</option>
        <template x-for="t in terms" :key="t.id">
            <option :value="t.id"
                    x-text="t.name + (t.academic_year ? ' (' + t.academic_year.name + ')' : '')"></option>
        </template>
    </select>
</div>

{{-- Role --}}
<div class="flex flex-col gap-1.5">
    <label class="block text-sm font-medium text-text-dark mb-1.5">Counts Toward</label>
    <select name="exam_role" x-model="form.exam_role"
            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
        <option value="none">Not counted toward final grade</option>
        <option value="ca">Continuous Assessment (CA)</option>
        <option value="end_of_term">End of Term Exam</option>
    </select>
    <p class="text-xs text-text-muted">Only one exam per term can be the End of Term Exam. CA-tagged exams are averaged and blended with it for the final grade.</p>
</div>

{{-- Date Range (2-col on md+) --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="flex flex-col gap-1.5">
        <label class="block text-sm font-medium text-text-dark mb-1.5">Start Date</label>
        <input type="date" name="start_date" x-model="form.start_date"
               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
    </div>
    <div class="flex flex-col gap-1.5">
        <label class="block text-sm font-medium text-text-dark mb-1.5">End Date</label>
        <input type="date" name="end_date" x-model="form.end_date"
               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
    </div>
</div>
