define([
    'jquery',
    "Eos_Base/node_modules/jquery-ui/dist/jquery-ui"
], function ($) {

    function modalProductCatalog(products) {
        // Filter products and update product dropdown options when category changes
        $('#categoryDropdown').on('change', function () {
            filterProducts(products);
            toggleOtherInput()
        });
        $('#productDropdown').on('change', function () {
            toggleOtherInput();
            $('#product_code').val($(this).val())
        });

        $('#searchInput').autocomplete({
            source: function (request, response) {

                var searchQuery = request.term.toLowerCase();
                var suggestions = [];

                products.forEach(function (product) {
                    if (
                        product.category.toLowerCase().includes(searchQuery) ||
                        product.product.toLowerCase().includes(searchQuery) ||
                        product.hs_cn.toLowerCase().includes(searchQuery)
                    ) {
                        suggestions.push({
                            label: product.category + ', ' + product.product + ', ' + product.hs_cn,
                            value: product.category + ', ' + product.product + ', ' + product.hs_cn,
                            id: product.id
                        });
                    }
                });

                response(suggestions);
            },
            minLength: 1,
            select: function (event, ui) {
                $('#categoryDropdown').val("");
                populateProductOptions(products)
                $('#searchInput').val(ui.item.label);
                $('#product_code').val(ui.item.id)
            }
        })
    }


    // Function to filter products based on selected category, product, and search query
    function filterProducts(products) {
        populateProductOptions(
            products.filter(function (product) {
                var category = product.category || '';
                var productTitle = product.product || '';

                return ($('#categoryDropdown').val() === '' || category === $('#categoryDropdown').val()) &&
                    ($('#productDropdown').val() === '' || productTitle === $('#productDropdown').val());
            })
        );
    }
    // Function to populate the product dropdown options
    function populateProductOptions(products) {
        var productDropdown = $('#productDropdown');
        productDropdown.empty();
        productDropdown.append('<option value="">All Products</option>');

        products.forEach(function (product) {
            productDropdown.append('<option value="' + product.id + '">' + product.product + '</option>');
        });
    }

    function toggleOtherInput() {
        var selectedCategory = $('#categoryDropdown').val();
        var selectedProduct = $('#productDropdown').val();
        if(selectedCategory == 'Other' || selectedProduct == 'Other') {
            $('#otherInput').parent().show()
        } else {
            $('#otherInput').parent().hide()
        }
    }

    return {
        modalProductCatalog: modalProductCatalog
    };
})
