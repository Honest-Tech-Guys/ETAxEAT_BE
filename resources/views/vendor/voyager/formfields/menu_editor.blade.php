<!--
Full path: resources/views/vendor/voyager/formfields/menu_editor.blade.php
This file creates a dynamic menu editor for Voyager that launches in a modal.
It allows adding/removing categories and dishes, including an image for each dish.
The entire menu structure is saved as a single JSON object in a hidden textarea.
-->

@php
    // Decode the raw JSON from the database into a clean PHP array for use in the script.
    $rawMenuData = $dataTypeContent->getRawOriginal($row->field);
    
    // Initialize menuData as empty array
    $menuData = [];
    
    // Handle different data formats
    if (is_string($rawMenuData)) {
        // Remove any extra quotes at the beginning and end if it's double-encoded
        $cleanData = trim($rawMenuData, '"');
        
        // First, try to decode as JSON
        $menuData = json_decode($cleanData, true);
        
        // If that fails, it might be double-encoded, so try again
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try unescaping and decoding again
            $unescapedData = stripslashes($cleanData);
            $menuData = json_decode($unescapedData, true);
        }
        
        // If still failing, try one more level of decoding
        if (json_last_error() !== JSON_ERROR_NONE && is_string($menuData)) {
            $menuData = json_decode($menuData, true);
        }
    } else if (is_array($rawMenuData)) {
        $menuData = $rawMenuData;
    }
    
    // If the data is still invalid or null, default to an empty array to prevent errors.
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($menuData)) {
        $menuData = [];
    }
    
    // Clean up any escaped characters in the data
    if (is_array($menuData)) {
        array_walk_recursive($menuData, function(&$item) {
            if (is_string($item)) {
                $item = stripslashes($item);
            }
        });
    }
@endphp

<!-- This hidden textarea holds the final JSON data that Voyager will save -->
<textarea
    name="{{ $row->field }}"
    id="menu-json-output-{{ $row->field }}"
    class="hidden-menu-json-output"
    style="display: none;">{{ json_encode($menuData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</textarea>

<!-- 1. The button that launches the modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#menuEditorModal-{{ $row->field }}">
    <i class="voyager-list"></i> Edit Food Menu
</button>

<!-- A small preview area to show a summary of the menu -->
<div id="menu-preview-{{ $row->field }}" style="margin-top: 10px;"></div>

<!-- 2. The Modal structure -->
<div class="modal fade" id="menuEditorModal-{{ $row->field }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Menu Editor</h4>
            </div>
            <div class="modal-body">
                <!-- The dynamic editor content will be built here -->
                <div class="menu-categories">
                    <!-- Categories and dishes will be dynamically inserted here by JavaScript -->
                </div>
                <button type="button" class="btn btn-success add-category-btn" style="margin-top: 15px;">
                    <i class="voyager-plus"></i> Add Menu Category
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary save-menu-btn" data-dismiss="modal">Save Menu</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit more for modal to be fully ready
    setTimeout(function() {
        // --- ELEMENT SELECTORS ---
        const editorModal = document.getElementById('menuEditorModal-{{ $row->field }}');
        const categoriesContainer = editorModal ? editorModal.querySelector('.menu-categories') : null;
        const hiddenJsonOutput = document.getElementById('menu-json-output-{{ $row->field }}');
        const previewContainer = document.getElementById('menu-preview-{{ $row->field }}');
        const addCategoryBtn = editorModal ? editorModal.querySelector('.add-category-btn') : null;
        const saveMenuBtn = editorModal ? editorModal.querySelector('.save-menu-btn') : null;
        
        // Check if all elements exist
        if (!editorModal || !categoriesContainer || !addCategoryBtn || !saveMenuBtn) {
            console.error('Some required elements not found:', {
                editorModal: !!editorModal,
                categoriesContainer: !!categoriesContainer,
                addCategoryBtn: !!addCategoryBtn,
                saveMenuBtn: !!saveMenuBtn
            });
            return;
        }
        
        console.log('All elements found, initializing menu editor...');
    
        // --- STATE MANAGEMENT ---
        let categoryIndex = 0;
        let dishIndex = 0;

        // --- TEMPLATING FUNCTIONS ---

        /**
         * Generates the HTML for a new category panel.
         * @param {number} catIndex - The unique index for the category.
         * @param {object} [category={}] - Optional existing category data to populate the fields.
         * @returns {string} HTML string for the category.
         */
        function createCategoryHTML(catIndex, category = {}) {
            const categoryName = category.name || '';
            return `
                <div class="panel panel-info menu-category" data-category-index="${catIndex}">
                    <div class="panel-heading">
                        <input type="text" class="form-control menu-category-name" value="${categoryName}" placeholder="Category Name (e.g., Appetizers, Main Courses)">
                        <button type="button" class="btn btn-danger btn-sm remove-category-btn"><i class="voyager-trash"></i> Remove Category</button>
                    </div>
                    <div class="panel-body menu-dishes"></div>
                    <div class="panel-footer">
                        <button type="button" class="btn btn-primary btn-sm add-dish-btn"><i class="voyager-plus"></i> Add Dish</button>
                    </div>
                </div>`;
        }

        /**
         * Generates the HTML for a new dish item.
         * @param {number} dIndex - The unique index for the dish.
         * @param {object} [dish={}] - Optional existing dish data to populate the fields.
         * @returns {string} HTML string for the dish.
         */
        function createDishHTML(dIndex, dish = {}) {
            const priceRateOptions = ['$', '$$', '$$$'];
            const selectedRate = dish.price_rate || '$';
            return `
                <div class="well menu-dish" data-dish-index="${dIndex}">
                    <button type="button" class="btn btn-danger btn-xs pull-right remove-dish-btn">X</button>
                    <div class="row">
                        <div class="col-md-4"><label>Dish Name</label><input type="text" class="form-control menu-dish-name" value="${dish.name || ''}" placeholder="Dish Name"></div>
                        <div class="col-md-8"><label>Description</label><input type="text" class="form-control menu-dish-description" value="${dish.description || ''}" placeholder="A brief description"></div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-3"><label>Price</label><input type="number" step="0.01" class="form-control menu-dish-price" value="${dish.price || ''}" placeholder="e.g., 12.99"></div>
                        <div class="col-md-3"><label>Food Origin</label><input type="text" class="form-control menu-dish-food_origin" value="${dish.food_origin || ''}" placeholder="e.g., Italian"></div>
                        <div class="col-md-3"><label>Price Rate</label><select class="form-control menu-dish-price_rate">${priceRateOptions.map(o => `<option value="${o}" ${selectedRate === o ? 'selected' : ''}>${o}</option>`).join('')}</select></div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <label>Image</label>
                            <div class="input-group">
                                <input type="text" class="form-control menu-dish-image" value="${dish.image || ''}" placeholder="Select an image...">
                                <span class="input-group-btn">
                                    <button class="btn btn-primary select-image-btn" type="button" data-toggle="modal" data-target="#media_picker">
                                        <i class="voyager-images"></i> Browse
                                    </button>
                                </span>
                            </div>
                            <div class="image-preview" style="margin-top: 10px;">
                                ${dish.image ? `<img src="${dish.image.startsWith('http') || dish.image.startsWith('/') || dish.image.startsWith('data:') ? dish.image : '/storage/' + dish.image}" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px;" onerror="this.style.display='none'">` : ''}
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        // --- CORE LOGIC ---

        /**
         * Builds the menu data object from the current state of the modal's form fields.
         * @returns {Array} An array of category objects.
         */
        function buildMenuDataFromDOM() {
            const menuData = [];
            const categoryNodes = categoriesContainer.querySelectorAll('.menu-category');
            
            categoryNodes.forEach(catNode => {
                const categoryObject = {
                    name: catNode.querySelector('.menu-category-name').value,
                    dishes: []
                };

                const dishNodes = catNode.querySelectorAll('.menu-dish');
                dishNodes.forEach(dishNode => {
                    const dishObject = {
                        name: dishNode.querySelector('.menu-dish-name').value,
                        description: dishNode.querySelector('.menu-dish-description').value,
                        price: dishNode.querySelector('.menu-dish-price').value,
                        food_origin: dishNode.querySelector('.menu-dish-food_origin').value,
                        price_rate: dishNode.querySelector('.menu-dish-price_rate').value,
                        image: dishNode.querySelector('.menu-dish-image').value,
                    };
                    categoryObject.dishes.push(dishObject);
                });
                menuData.push(categoryObject);
            });
            return menuData;
        }

        /**
         * Takes the menu data object, stringifies it, and saves it to the hidden textarea.
         * Then updates the preview.
         */
        function saveMenuToTextarea() {
            const menuData = buildMenuDataFromDOM();
            // Use compact JSON without pretty printing to avoid formatting issues
            hiddenJsonOutput.value = JSON.stringify(menuData);
            console.log('Saving menu data:', menuData);
            console.log('JSON string length:', hiddenJsonOutput.value.length);
            updatePreview();
        }

        /**
         * Reads the data from the hidden textarea and updates the small preview summary.
         */
        function updatePreview() {
            try {
                const data = JSON.parse(hiddenJsonOutput.value);
                const categoryCount = data.length;
                const dishCount = data.reduce((total, cat) => total + (cat.dishes ? cat.dishes.length : 0), 0);
                previewContainer.innerHTML = `<span class="label label-success">${categoryCount} Categories</span> <span class="label label-info">${dishCount} Dishes</span>`;
            } catch (e) {
                previewContainer.innerHTML = '<span class="label label-warning">No menu data</span>';
            }
        }

        /**
         * Populates the modal editor with data from the hidden textarea.
         */
        function loadInitialData() {
            let data = @json($menuData);
            
            console.log('Raw data from PHP:', data); // Debug log
            console.log('Data type:', typeof data); // Debug log

            // Handle case where data might be a string instead of array
            if (typeof data === 'string') {
                console.log('Data is string, attempting to parse...'); // Debug log
                try {
                    // Try to parse the string
                    data = JSON.parse(data);
                    console.log('First parse successful:', data); // Debug log
                } catch (e) {
                    console.log('First parse failed, trying to clean and parse again...'); // Debug log
                    try {
                        // Try to clean the string and parse again
                        let cleanData = data.replace(/\\\\/g, '\\').replace(/\\"/g, '"');
                        data = JSON.parse(cleanData);
                        console.log('Second parse successful:', data); // Debug log
                    } catch (e2) {
                        console.error('Failed to parse menu data:', e2);
                        data = [];
                    }
                }
            }

            if (!Array.isArray(data)) {
                console.warn('Menu data is not an array:', data);
                console.log('Attempting to extract array from object...'); // Debug log
                
                // Sometimes the data might be wrapped in an object or have extra structure
                if (data && typeof data === 'object') {
                    // Check if it has a property that contains the array
                    const keys = Object.keys(data);
                    for (let key of keys) {
                        if (Array.isArray(data[key])) {
                            data = data[key];
                            console.log('Found array in property:', key, data); // Debug log
                            break;
                        }
                    }
                }
                
                // Final fallback
                if (!Array.isArray(data)) {
                    console.log('Using empty array as fallback'); // Debug log
                    data = [];
                }
            }

            console.log('Final data to load:', data); // Debug log

            categoriesContainer.innerHTML = ''; // Clear existing
            categoryIndex = 0;
            dishIndex = 0;

            data.forEach((category, catIdx) => {
                console.log(`Loading category ${catIdx}:`, category); // Debug log
                
                const catNodeHTML = createCategoryHTML(categoryIndex, category);
                const catNode = document.createElement('div');
                catNode.innerHTML = catNodeHTML;
                const finalCatNode = catNode.firstElementChild;

                const dishesContainer = finalCatNode.querySelector('.menu-dishes');
                if (category.dishes && Array.isArray(category.dishes)) {
                    category.dishes.forEach((dish, dishIdx) => {
                        console.log(`Loading dish ${dishIdx}:`, dish); // Debug log
                        
                        const dishNodeHTML = createDishHTML(dishIndex, dish);
                        const dishNode = document.createElement('div');
                        dishNode.innerHTML = dishNodeHTML;
                        const dishElement = dishNode.firstElementChild;
                        dishesContainer.appendChild(dishElement);
                        
                        // Initialize image picker for existing dishes
                        initializeImagePicker(dishElement);
                        
                        dishIndex++;
                    });
                }
                
                categoriesContainer.appendChild(finalCatNode);
                categoryIndex++;
            });
            
            console.log(`Loaded ${categoryIndex} categories with ${dishIndex} total dishes`); // Debug log
            updatePreview();
        }

        // --- EVENT LISTENERS ---
        
        // Fixed: Separate event listener for the main "Add Category" button
        addCategoryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Add category button clicked'); // Debug log
            
            const categoryHTML = createCategoryHTML(categoryIndex);
            console.log('Generated HTML:', categoryHTML); // Debug log
            
            // Create a temporary container to parse the HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = categoryHTML;
            const categoryElement = tempDiv.firstElementChild;
            
            if (categoryElement) {
                categoriesContainer.appendChild(categoryElement);
                categoryIndex++;
                console.log('Category added, new index:', categoryIndex); // Debug log
                
                // Re-initialize Voyager media picker for new elements
                if (typeof voyager !== 'undefined' && voyager.media) {
                    voyager.media.init();
                }
            } else {
                console.error('Failed to create category element');
            }
        });

        // FIXED EVENT DELEGATION - Put dish removal first and most specific
        categoriesContainer.addEventListener('click', function(e) {
            console.log('Click detected on:', e.target, 'Classes:', e.target.className); // Debug log
            
            // Handle remove dish button - check this FIRST and most specifically
            if (e.target.classList.contains('remove-dish-btn') || 
                e.target.parentElement?.classList.contains('remove-dish-btn')) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Remove dish button clicked');
                
                // Find the dish element to remove
                let dishToRemove = e.target.closest('.menu-dish');
                if (!dishToRemove && e.target.parentElement) {
                    dishToRemove = e.target.parentElement.closest('.menu-dish');
                }
                
                if (dishToRemove) {
                    // Add a confirmation (optional - remove if you don't want confirmation)
                    if (confirm('Are you sure you want to remove this dish?')) {
                        dishToRemove.remove();
                        console.log('Dish removed successfully');
                    }
                } else {
                    console.error('Could not find dish element to remove');
                }
                return; // Exit early to prevent other handlers
            }
            
            if (e.target.closest('.remove-category-btn')) {
                e.preventDefault();
                console.log('Remove category clicked');
                const categoryToRemove = e.target.closest('.menu-category');
                if (categoryToRemove && confirm('Are you sure you want to remove this category and all its dishes?')) {
                    categoryToRemove.remove();
                }
                return;
            }
            
            if (e.target.closest('.add-dish-btn')) {
                e.preventDefault();
                console.log('Add dish clicked');
                const categoryNode = e.target.closest('.menu-category');
                const dishesContainer = categoryNode.querySelector('.menu-dishes');
                
                const dishHTML = createDishHTML(dishIndex);
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = dishHTML;
                const dishElement = tempDiv.firstElementChild;
                
                if (dishElement) {
                    dishesContainer.appendChild(dishElement);
                    dishIndex++;
                    
                    // Initialize image picker for the new dish
                    initializeImagePicker(dishElement);
                }
                return;
            }
            
            if (e.target.closest('.select-image-btn')) {
                e.preventDefault();
                console.log('Select image clicked');
                openMediaPicker(e.target);
                return;
            }
        });

        // Function to initialize image picker for a dish element
        function initializeImagePicker(dishElement) {
            const imageInput = dishElement.querySelector('.menu-dish-image');
            const imagePreview = dishElement.querySelector('.image-preview');
            
            // Update preview when image URL changes
            imageInput.addEventListener('input', function() {
                updateImagePreview(this.value, imagePreview);
            });
        }

        // Function to update image preview
        function updateImagePreview(imageUrl, previewContainer) {
            if (imageUrl) {
                // Handle different image URL formats
                let displayUrl = imageUrl;
                
                // If it's a relative path, prepend /storage/
                if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/storage/') && !imageUrl.startsWith('data:')) {
                    displayUrl = '/storage/' + imageUrl;
                }
                
                previewContainer.innerHTML = `<img src="${displayUrl}" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px;" onerror="this.style.display='none'">`;
            } else {
                previewContainer.innerHTML = '';
            }
        }

        // Function to open Voyager media picker
        function openMediaPicker(button) {
            const dishElement = button.closest('.menu-dish');
            const imageInput = dishElement.querySelector('.menu-dish-image');
            const imagePreview = dishElement.querySelector('.image-preview');
            
            // Store reference for the media picker callback
            window.currentImageInput = imageInput;
            window.currentImagePreview = imagePreview;
            
            // Try different approaches to open Voyager media picker
            if (typeof voyager !== 'undefined' && voyager.media) {
                // Method 1: Direct media picker
                if (voyager.media.picker && typeof voyager.media.picker.show === 'function') {
                    voyager.media.picker.show(function(data) {
                        handleMediaSelection(data, imageInput, imagePreview);
                    });
                    return;
                }
                
                // Method 2: Initialize and show media picker
                if (typeof voyager.media.init === 'function') {
                    voyager.media.init();
                    setTimeout(() => {
                        if (voyager.media.picker && typeof voyager.media.picker.show === 'function') {
                            voyager.media.picker.show(function(data) {
                                handleMediaSelection(data, imageInput, imagePreview);
                            });
                        }
                    }, 100);
                    return;
                }
            }
            
            // Method 3: Try to trigger existing media modal
            const mediaModal = document.getElementById('media_picker');
            if (mediaModal) {
                $(mediaModal).modal('show');
                return;
            }
            
            // Method 4: Look for existing media picker buttons
            const existingMediaBtn = document.querySelector('.voyager-media-picker a, .media-picker-btn');
            if (existingMediaBtn) {
                existingMediaBtn.click();
                return;
            }
            
            // Fallback: Manual file upload
            console.log('Using fallback file upload');
            uploadNewImage(imageInput, imagePreview);
        }

        // Handle media selection from Voyager
        function handleMediaSelection(data, imageInput, imagePreview) {
            if (data && data.length > 0) {
                const selectedImage = data[0];
                let imagePath = selectedImage.url || selectedImage.path || selectedImage.relative_path || selectedImage;
                
                // Clean the path - remove domain and /storage/ prefix for database storage
                if (imagePath.includes('/storage/')) {
                    imagePath = imagePath.split('/storage/')[1];
                }
                
                imageInput.value = imagePath;
                updateImagePreview('/storage/' + imagePath, imagePreview);
            }
        }

        // Global function for media picker callback (if Voyager uses global callback)
        window.mediaPickerCallback = function(data) {
            if (window.currentImageInput && window.currentImagePreview) {
                handleMediaSelection(data, window.currentImageInput, window.currentImagePreview);
                window.currentImageInput = null;
                window.currentImagePreview = null;
            }
        };

        // Function to handle new image upload (fallback)
        function uploadNewImage(imageInput, imagePreview) {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.style.display = 'none';
            
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Create a simple filename for storage
                    const timestamp = Date.now();
                    const fileName = `${timestamp}_${file.name}`;
                    
                    // For now, just show the filename - you'll need to implement actual upload
                    imageInput.value = 'uploads/' + fileName;
                    
                    // Show preview using FileReader
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        updateImagePreview(e.target.result, imagePreview);
                    };
                    reader.readAsDataURL(file);
                    
                    console.log('File selected:', fileName, 'You need to implement server upload');
                }
                document.body.removeChild(fileInput);
            });
            
            document.body.appendChild(fileInput);
            fileInput.click();
        }

        // Save button event listener
        saveMenuBtn.addEventListener('click', saveMenuToTextarea);

        // --- INITIALIZATION ---
        loadInitialData();

        // Observer to re-initialize media picker when new elements are added
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes.length) {
                    if (typeof voyager !== 'undefined' && voyager.media) {
                        voyager.media.init();
                    }
                }
            });
        });
        observer.observe(categoriesContainer, { childList: true, subtree: true });
        
    }, 100); // Wait 100ms for DOM to be fully ready
});
</script>

<style>
    .modal-dialog.modal-lg { width: 80%; }
    .menu-category .panel-heading { display: flex; align-items: center; justify-content: space-between; }
    .menu-category .panel-heading input { flex-grow: 1; margin-right: 15px; }
    .menu-dish { padding-top: 25px; position: relative; background-color: #f9f9f9; border: 1px solid #ddd; margin-bottom: 10px; }
    .menu-dish .remove-dish-btn { 
        position: absolute; 
        top: 5px; 
        right: 5px; 
        z-index: 10; 
        cursor: pointer;
        background: #d9534f;
        color: white;
        border: none;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
        line-height: 1;
        min-width: 20px;
        height: 20px;
    }
    .menu-dish .remove-dish-btn:hover {
        background: #c9302c;
        transform: scale(1.1);
    }
    .menu-dish .remove-dish-btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(217, 83, 79, 0.5);
    }
</style>