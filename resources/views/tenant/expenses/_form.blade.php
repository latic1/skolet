{{--
  Shared form fields for both Add and Edit expense modals.
  Relies on the parent `expensesPage` Alpine component for `form.*` bindings.
--}}

{{-- Sentinel fields for validation error re-open --}}
<input type="hidden" name="_expense_mode" :value="mode">
<input type="hidden" name="_expense_id" :value="mode === 'edit' ? editAction.split('/').pop() : ''">

<div class="flex flex-col gap-5">

    {{-- Category + Add Category inline --}}
    <div class="flex flex-col gap-1.5">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-text-dark">Category <span class="text-error">*</span></label>
            @can('expenses.create')
            <button type="button" @click.prevent="showCategoryModal = true"
                    class="text-xs font-medium text-accent hover:text-accent-dark transition-colors">
                + Add Category
            </button>
            @endcan
        </div>
        <select name="category_id" x-model="form.category_id" required
                class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('category_id') ? 'border-error focus:ring-error' : '' }}">
            <option value="">Select category&hellip;</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('category_id')
        <p class="text-xs text-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Amount + Date (2-col) --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="block text-sm font-medium text-text-dark">Amount <span class="text-error">*</span></label>
            <input type="number" name="amount" x-model="form.amount" required min="0.01" step="0.01"
                   placeholder="0.00"
                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('amount') ? 'border-error focus:ring-error' : '' }}">
            @error('amount')
            <p class="text-xs text-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="block text-sm font-medium text-text-dark">Date <span class="text-error">*</span></label>
            <input type="date" name="date" x-model="form.date" required
                   max="{{ now()->format('Y-m-d') }}"
                   class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('date') ? 'border-error focus:ring-error' : '' }}">
            @error('date')
            <p class="text-xs text-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Description --}}
    <div class="flex flex-col gap-1.5">
        <label class="block text-sm font-medium text-text-dark">Description <span class="text-error">*</span></label>
        <input type="text" name="description" x-model="form.description" required maxlength="255"
               placeholder="e.g. Monthly electricity bill"
               class="w-full px-3 py-2 bg-surface border border-border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-accent {{ $errors->has('description') ? 'border-error focus:ring-error' : '' }}">
        @error('description')
        <p class="text-xs text-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Receipt upload (optional) --}}
    <div class="flex flex-col gap-1.5">
        <label class="block text-sm font-medium text-text-dark">Receipt <span class="text-xs text-text-muted font-normal">(optional &mdash; JPG, PNG, PDF &middot; max 5 MB)</span></label>
        <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf"
               class="w-full text-sm text-text-secondary file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-accent-muted file:text-accent hover:file:bg-accent-light transition-colors">
        @error('receipt')
        <p class="text-xs text-error">{{ $message }}</p>
        @enderror
    </div>

</div>
