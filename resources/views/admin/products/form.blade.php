@extends('admin.layout')

@section('title', $product->exists ? 'Edit product' : 'Add product')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $product->exists ? 'Edit product' : 'Add product' }}</h1>
            <p>{{ $product->exists ? 'Update product details and inventory.' : 'Add a new item to your store catalog.' }}</p>
        </div>
        <a class="secondary-button" href="{{ route('admin.products.index') }}">Back to products</a>
    </div>

    @if ($errors->any())
        <div class="form-alert" role="alert">Please check the highlighted fields and try again.</div>
    @endif

    <form
        class="product-form"
        method="POST"
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
        enctype="multipart/form-data"
    >
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="form-column">
            <section class="panel form-panel">
                <div class="form-panel-heading">
                    <h2 class="panel-title">Product image</h2>
                    <p class="panel-subtitle">Upload a clear product photo (JPG, PNG, or WebP; up to 2 MB).</p>
                </div>
                <label class="image-drop" for="image">
                    @if ($product->image_path)
                        <img id="image-preview" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="Current product image">
                    @else
                        <img id="image-preview" alt="Product image preview" hidden>
                        <span id="image-placeholder"><span class="upload-icon">+</span><strong>Choose a product image</strong><span>Click to browse your files</span></span>
                    @endif
                    <input type="file" id="image" name="image" accept="image/*">
                </label>
                @error('image')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </section>

            <section class="panel form-panel">
                <div class="form-panel-heading">
                    <h2 class="panel-title">Product details</h2>
                    <p class="panel-subtitle">Describe the item customers will find in your store.</p>
                </div>
                <div class="form-field">
                    <label for="name">Product name <span>*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="255" required>
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-two">
                    <div class="form-field">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" value="{{ old('brand', $product->brand) }}" maxlength="255" placeholder="e.g. Vaporesso">
                        @error('brand')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-field">
                        <label for="category">Category <span>*</span></label>
                        <input type="text" id="category" name="category" value="{{ old('category', $product->category) }}" maxlength="100" placeholder="e.g. Pod Systems" required>
                        @error('category')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" maxlength="5000" placeholder="Add product details...">{{ old('description', $product->description) }}</textarea>
                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </section>
        </div>

        <div class="form-column">
            <section class="panel form-panel">
                <div class="form-panel-heading">
                    <h2 class="panel-title">Pricing and inventory</h2>
                    <p class="panel-subtitle">Set the selling price and available stock.</p>
                </div>
                <div class="form-field">
                    <label for="price">Price (PHP) <span>*</span></label>
                    <div class="input-prefix"><span>₱</span><input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" min="0" max="99999999.99" step="0.01" required></div>
                    @error('price')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="stock">Stock quantity <span>*</span></label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" min="0" step="1" required>
                    @error('stock')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <label class="active-toggle" for="is_active">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked($errors->any() ? old('is_active') : ($product->exists ? $product->is_active : true))>
                    <span><strong>Visible in store</strong><small>Active products can be shown to customers.</small></span>
                </label>
            </section>

            <section class="panel form-panel form-actions">
                <button class="primary-button save-button" type="submit">{{ $product->exists ? 'Save changes' : 'Add product' }}</button>
                <a class="secondary-button cancel-button" href="{{ route('admin.products.index') }}">Cancel</a>
                @if ($product->exists)
                    <button class="delete-button" type="submit" form="delete-product">Delete product</button>
                @endif
            </section>
        </div>
    </form>

    @if ($product->exists)
        <form id="delete-product" method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product? This cannot be undone.');">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection

@push('styles')
    <style>
        .form-alert { margin-bottom: 14px; padding: 10px 12px; border: 1px solid rgba(255, 102, 127, .3); border-radius: 6px; background: rgba(130, 30, 50, .15); color: #ff9aa7; font-size: 10px; }
        .product-form { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(250px, .85fr); align-items: start; gap: 13px; }
        .form-column { display: grid; gap: 13px; }
        .form-panel { padding: 15px; }
        .form-panel-heading { margin-bottom: 16px; }
        .form-field { margin-top: 13px; }
        .form-field:first-of-type { margin-top: 0; }
        .form-field label { display: block; margin-bottom: 7px; color: #bcb6c7; font-size: 10px; font-weight: 600; }
        .form-field label span { color: #b397ff; }
        .form-field input, .form-field textarea { display: block; width: 100%; min-height: 35px; padding: 8px 10px; border: 1px solid #302a3b; border-radius: 5px; outline: 0; background: #121019; color: #f0edf5; font: inherit; font-size: 10px; transition: border-color .18s, box-shadow .18s; }
        .form-field textarea { min-height: 98px; resize: vertical; line-height: 1.55; }
        .form-field input:focus, .form-field textarea:focus { border-color: #7045eb; box-shadow: 0 0 0 2px rgba(112, 69, 235, .15); }
        .form-field input::placeholder, .form-field textarea::placeholder { color: #696373; }
        .form-two { display: grid; grid-template-columns: 1fr 1fr; gap: 11px; }
        .input-prefix { position: relative; }
        .input-prefix > span { position: absolute; top: 9px; left: 10px; color: #958b9f; font-size: 11px; }
        .input-prefix input { padding-left: 26px; }
        .image-drop { display: grid; min-height: 155px; place-items: center; overflow: hidden; border: 1px dashed #45375f; border-radius: 6px; background: linear-gradient(145deg, rgba(42, 31, 65, .25), rgba(18, 16, 25, .6)); color: #958b9f; cursor: pointer; transition: border-color .18s, background .18s; }
        .image-drop:hover { border-color: #8a67e7; background: rgba(51, 36, 82, .25); }
        .image-drop > img:not([hidden]) { display: block; width: 100%; height: 210px; object-fit: contain; background: #100e16; }
        .image-drop input { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; clip-path: inset(50%); }
        .image-drop:focus-within { outline: 2px solid #8357ff; outline-offset: 2px; }
        #image-placeholder { display: grid; justify-items: center; gap: 7px; padding: 20px; text-align: center; }
        #image-placeholder strong { color: #c5becf; font-size: 10px; }
        #image-placeholder > span:last-child { color: #7d7688; font-size: 9px; }
        .upload-icon { display: grid; width: 28px; height: 28px; place-items: center; border: 1px solid #493a68; border-radius: 50%; color: #b49bff; font-size: 18px; }
        .active-toggle { display: flex; align-items: flex-start; gap: 9px; margin-top: 19px; padding: 11px; border: 1px solid #2c2735; border-radius: 5px; cursor: pointer; }
        .active-toggle input { margin: 2px 0 0; accent-color: #7045eb; }
        .active-toggle strong, .active-toggle small { display: block; }
        .active-toggle strong { color: #ddd7e7; font-size: 10px; }
        .active-toggle small { margin-top: 4px; color: #7f7889; font-size: 9px; line-height: 1.4; }
        .form-actions { display: grid; gap: 8px; }
        .form-actions .primary-button, .form-actions .secondary-button { width: 100%; min-height: 35px; }
        .delete-button { min-height: 32px; border: 0; background: transparent; color: #e77b88; font: inherit; font-size: 9px; cursor: pointer; }
        .delete-button:hover { color: #ff9aa7; }
        @media (max-width: 780px) { .product-form { grid-template-columns: 1fr; } }
        @media (max-width: 460px) { .form-two { grid-template-columns: 1fr; gap: 0; } }
    </style>
@endpush

@push('scripts')
    <script>
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('image-preview');
        const imagePlaceholder = document.getElementById('image-placeholder');

        imageInput?.addEventListener('change', () => {
            const [file] = imageInput.files;
            if (!file) return;

            imagePreview.src = URL.createObjectURL(file);
            imagePreview.hidden = false;
            if (imagePlaceholder) imagePlaceholder.hidden = true;
        });
    </script>
@endpush
