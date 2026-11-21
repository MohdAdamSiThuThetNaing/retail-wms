
<div class="form-grid">
    <div class="form-group">
        <label for="customer_code">Customer Code *</label>
        <input type="text" id="customer_code" name="customer_code"
               value="{{ old('customer_code', $customer->customer_code ?? '') }}"
               maxlength="30" required>
        @error('customer_code') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="customer_type">Customer Type *</label>
        <select id="customer_type" name="customer_type" required>
            @foreach (['individual', 'retail', 'wholesale', 'corporate'] as $type)
                <option value="{{ $type }}"
                    @selected(old('customer_type', $customer->customer_type ?? 'individual') === $type)>
                    {{ ucfirst($type) }}
                </option>
            @endforeach
        </select>
        @error('customer_type') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="name">Customer Name *</label>
        <input type="text" id="name" name="name"
               value="{{ old('name', $customer->name ?? '') }}"
               maxlength="180" required>
        @error('name') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="company_name">Company Name</label>
        <input type="text" id="company_name" name="company_name"
               value="{{ old('company_name', $customer->company_name ?? '') }}"
               maxlength="180">
        @error('company_name') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="contact_person">Contact Person</label>
        <input type="text" id="contact_person" name="contact_person"
               value="{{ old('contact_person', $customer->contact_person ?? '') }}"
               maxlength="120">
        @error('contact_person') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               value="{{ old('email', $customer->email ?? '') }}"
               maxlength="180">
        @error('email') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone"
               value="{{ old('phone', $customer->phone ?? '') }}"
               maxlength="50">
        @error('phone') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="tax_number">Tax Number</label>
        <input type="text" id="tax_number" name="tax_number"
               value="{{ old('tax_number', $customer->tax_number ?? '') }}"
               maxlength="100">
        @error('tax_number') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="credit_limit">Credit Limit</label>
        <input type="number" id="credit_limit" name="credit_limit"
               value="{{ old('credit_limit', $customer->credit_limit ?? '0.00') }}"
               min="0" step="0.01" required>
        @error('credit_limit') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="payment_terms">Payment Terms *</label>
        <select id="payment_terms" name="payment_terms" required>
            @foreach (['cash' => 'Cash', 'net_15' => 'Net 15 Days', 'net_30' => 'Net 30 Days', 'net_60' => 'Net 60 Days'] as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('payment_terms', $customer->payment_terms ?? 'cash') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('payment_terms') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group full-width">
        <label for="address">Address</label>
        <textarea id="address" name="address" rows="3">{{ old('address', $customer->address ?? '') }}</textarea>
        @error('address') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group full-width">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="3">{{ old('notes', $customer->notes ?? '') }}</textarea>
        @error('notes') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group full-width">
        <input type="hidden" name="is_active" value="0">
        <label>
            <input type="checkbox" name="is_active" value="1"
                @checked((bool) old('is_active', $customer->is_active ?? true))>
            Active Customer
        </label>
        @error('is_active') <small class="error-text">{{ $message }}</small> @enderror
    </div>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
</div>
