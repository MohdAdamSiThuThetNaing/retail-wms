<div class="panel product-panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Warehouse Information</div>
            <div class="panel-subtitle">Enter the warehouse details below</div>
        </div>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label for="code">Warehouse Code *</label>
            <input
                id="code"
                type="text"
                name="code"
                class="form-control"
                value="{{ old('code', $warehouse?->code) }}"
                placeholder="e.g. WH-BKK-001"
                maxlength="32"
                required
            >
        </div>

        <div class="form-group">
            <label for="name">Warehouse Name *</label>
            <input
                id="name"
                type="text"
                name="name"
                class="form-control"
                value="{{ old('name', $warehouse?->name) }}"
                placeholder="Enter warehouse name"
                maxlength="150"
                required
            >
        </div>

        <div class="form-group">
            <label for="contact_person">Contact Person</label>
            <input
                id="contact_person"
                type="text"
                name="contact_person"
                class="form-control"
                value="{{ old('contact_person', $warehouse?->contact_person) }}"
                placeholder="Contact person's name"
                maxlength="120"
            >
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input
                id="phone"
                type="text"
                name="phone"
                class="form-control"
                value="{{ old('phone', $warehouse?->phone) }}"
                placeholder="Phone number"
                maxlength="40"
            >
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email', $warehouse?->email) }}"
                placeholder="warehouse@example.com"
                maxlength="150"
            >
        </div>

        <div class="form-group form-full">
            <label for="address">Warehouse Address</label>
            <textarea
                id="address"
                name="address"
                class="form-control"
                rows="3"
                placeholder="Enter complete warehouse address"
            >{{ old('address', $warehouse?->address) }}</textarea>
        </div>

        <div class="form-group form-full">
            <label for="notes">Notes</label>
            <textarea
                id="notes"
                name="notes"
                class="form-control"
                rows="3"
                placeholder="Additional information"
            >{{ old('notes', $warehouse?->notes) }}</textarea>
        </div>

        <div class="form-group">
            <label for="is_active">Warehouse Status</label>
            <select id="is_active" name="is_active" class="form-control">
                <option value="1" @selected((string) old('is_active', $warehouse?->is_active ?? 1) === '1')>Active</option>
                <option value="0" @selected((string) old('is_active', $warehouse?->is_active ?? 1) === '0')>Inactive</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>
