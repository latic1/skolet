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
    <label class="block text-sm font-medium text-text-dark mb-1.5">Term</label>
    <select name="term_id" x-model="form.term_id"
            class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent transition-colors">
        <option value="">No term selected</option>
        <template x-for="t in terms" :key="t.id">
            <option :value="t.id"
                    x-text="t.name + (t.academic_year ? ' (' + t.academic_year.name + ')' : '')"></option>
        </template>
    </select>
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
