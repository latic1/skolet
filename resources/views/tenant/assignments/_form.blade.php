<div class="flex flex-col gap-4">
    {{-- Title --}}
    <div>
        <label class="block text-sm font-medium text-text-dark mb-1.5">Title <span class="text-error">*</span></label>
        <input type="text" name="title" x-model="form.title" required
               value="{{ old('title') }}"
               class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors
                      {{ $errors->has('title') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}">
        @error('title')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-medium text-text-dark mb-1.5">Description / Instructions <span class="text-error">*</span></label>
        <textarea name="description" x-model="form.description" rows="3" required
                  class="w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors resize-y
                         {{ $errors->has('description') ? 'border-error focus:ring-error focus:border-error' : 'border-border focus:ring-accent focus:border-accent' }}"
                  placeholder="Describe the task, requirements, and any resources…">{{ old('description') }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Subject + Class (2-col) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-text-dark mb-1.5">Subject <span class="text-error">*</span></label>
            <select name="subject_id" x-model="form.subject_id" required
                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                <option value="">Select subject</option>
                <template x-for="subject in subjects" :key="subject.id">
                    <option :value="subject.id" :selected="form.subject_id === subject.id" x-text="subject.name"></option>
                </template>
            </select>
            @error('subject_id')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-text-dark mb-1.5">Class <span class="text-error">*</span></label>
            <select name="class_id" x-model="form.class_id" @change="form.section_id = ''" required
                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                <option value="">Select class</option>
                <template x-for="cls in classes" :key="cls.id">
                    <option :value="cls.id" :selected="form.class_id === cls.id" x-text="cls.name"></option>
                </template>
            </select>
            @error('class_id')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Section (conditional) --}}
    <div x-show="hasSections">
        <label class="block text-sm font-medium text-text-dark mb-1.5">Section <span class="text-xs text-text-muted">(optional — leave blank for all sections)</span></label>
        <select name="section_id" x-model="form.section_id"
                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
            <option value="">All sections</option>
            <template x-for="section in currentSections" :key="section.id">
                <option :value="section.id" :selected="form.section_id === section.id" x-text="section.name"></option>
            </template>
        </select>
    </div>

    {{-- Admin teacher selector --}}
    <template x-if="canManageAll">
        <div>
            <label class="block text-sm font-medium text-text-dark mb-1.5">Assign Teacher</label>
            <select name="teacher_id"
                    class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
                <option value="">Self (auto-assigned)</option>
                <template x-for="member in staff" :key="member.id">
                    <option :value="member.id" x-text="member.full_name"></option>
                </template>
            </select>
        </div>
    </template>

    {{-- Due Date + Total Marks (2-col) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-text-dark mb-1.5">Due Date & Time <span class="text-error">*</span></label>
            <input type="datetime-local" name="due_date" x-model="form.due_date" required
                   value="{{ old('due_date') }}"
                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
            @error('due_date')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-text-dark mb-1.5">Total Marks <span class="text-xs text-text-muted">(optional)</span></label>
            <input type="number" name="total_marks" x-model="form.total_marks" min="0" step="0.5"
                   value="{{ old('total_marks') }}"
                   placeholder="e.g. 100"
                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent focus:border-accent">
            @error('total_marks')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
