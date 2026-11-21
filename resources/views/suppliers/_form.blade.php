
<div class="form-grid">

    <div class="form-group">
        <label for="supplier_code">Supplier Code *</label>
        <input
            id="supplier_code"
            name="supplier_code"
            class="form-control"
            value="{{ old('supplier_code', $supplier->supplier_code ?? '') }}"
            placeholder="SUP-0001"
            maxlength="30"
            required
        >
        @error('supplier_code') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="company_name">Company Name *</label>
        <input
            id="company_name"
            name="company_name"
            class="form-control"
            value="{{ old('company_name', $supplier->company_name ?? '') }}"
            placeholder="Company name"
            maxlength="180"
            required
        >
        @error('company_name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="contact_person">Contact Person</label>
        <input
            id="contact_person"
            name="contact_person"
            class="form-control"
            value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
        >
        @error('contact_person') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-control"
            value="{{ old('email', $supplier->email ?? '') }}"
        >
        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="phone">Phone</label>
        <input
            id="phone"
            name="phone"
            class="form-control"
            value="{{ old('phone', $supplier->phone ?? '') }}"
        >
        @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="tax_number">Tax Number</label>
        <input
            id="tax_number"
            name="tax_number"
            class="form-control"
            value="{{ old('tax_number', $supplier->tax_number ?? '') }}"
        >
        @error('tax_number') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="payment_terms">Payment Terms *</label>
        <select id="payment_terms" name="payment_terms" class="form-control" required>
            @foreach([
                'cash' => 'Cash',
                'net_15' => 'Net 15 Days',
                'net_30' => 'Net 30 Days',
                'net_60' => 'Net 60 Days',
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('payment_terms', $supplier->payment_terms ?? 'cash') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('payment_terms') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="is_active">Status *</label>
        <select id="is_active" name="is_active" class="form-control" required>
            <option value="1" @selected((string) old('is_active', isset($supplier) ? (int) $supplier->is_active : 1) === '1')>
                Active
            </option>
            <option value="0" @selected((string) old('is_active', isset($supplier) ? (int) $supplier->is_active : 1) === '0')>
                Inactive
            </option>
        </select>
        @error('is_active') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group form-full">
        <label for="address">Business Address</label>
        <textarea
            id="address"
            name="address"
            class="form-control"
            rows="3"
        >{{ old('address', $supplier->address ?? '') }}</textarea>
        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="form-group form-full">
        <label for="notes">Notes</label>
        <textarea
            id="notes"
            name="notes"
            class="form-control"
            rows="3"
        >{{ old('notes', $supplier->notes ?? '') }}</textarea>
        @error('notes') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

</div>
