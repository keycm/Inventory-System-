<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="pos-container" style="display: flex; gap: 20px;">
    <!-- Left: Product Selection -->
    <div class="product-selection" style="flex: 2;">
        <div class="search-bar" style="margin-bottom: 20px;">
            <input type="text" id="product-search" placeholder="Search Product by Name or SKU..." style="width: 100%; padding: 10px; font-size: 1.2rem;">
        </div>
        <div id="product-results" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
            <!-- Products will be loaded here via AJAX -->
        </div>
    </div>

    <!-- Right: Cart -->
    <div class="cart-section" style="flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); height: fit-content;">
        <h3>Current Order</h3>
        <table id="cart-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>X</th>
                </tr>
            </thead>
            <tbody>
                <!-- Cart items -->
            </tbody>
        </table>
        <div style="margin-top: 20px; text-align: right;">
            <h3>Total: <span id="cart-total">₱0.00</span></h3>
            <button id="checkout-btn" class="btn btn-success" style="width: 100%; font-size: 1.2rem;">Checkout</button>
        </div>
    </div>
</div>

<script>
    let cart = [];

    // Search Products
    document.getElementById('product-search').addEventListener('input', function(e) {
        const query = e.target.value;
        if (query.length > 2) {
            fetch('search_products.php?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(products => {
                    const results = document.getElementById('product-results');
                    results.innerHTML = '';
                    products.forEach(product => {
                        const div = document.createElement('div');
                        div.className = 'product-card';
                        div.style.border = '1px solid #ddd';
                        div.style.padding = '10px';
                        div.style.cursor = 'pointer';
                        div.style.borderRadius = '4px';
                        div.style.textAlign = 'center';
                        div.innerHTML = `
                            <h4>${product.name}</h4>
                            <p>₱${product.price}</p>
                            <small>Stock: ${product.stock}</small>
                        `;
                        div.onclick = () => addToCart(product);
                        results.appendChild(div);
                    });
                });
        }
    });

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            if (existing.quantity < product.stock) {
                existing.quantity++;
            } else {
                alert('Not enough stock!');
            }
        } else {
            if (product.stock > 0) {
                cart.push({ ...product, quantity: 1 });
            } else {
                alert('Out of stock!');
            }
        }
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function renderCart() {
        const tbody = document.querySelector('#cart-table tbody');
        tbody.innerHTML = '';
        let total = 0;
        cart.forEach((item, index) => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.name}</td>
                <td><input type="number" min="1" max="${item.stock}" value="${item.quantity}" onchange="updateQty(${index}, this.value)" style="width: 50px;"></td>
                <td>₱${item.price}</td>
                <td>₱${subtotal.toFixed(2)}</td>
                <td><button onclick="removeFromCart(${index})" class="btn btn-danger btn-sm">X</button></td>
            `;
            tbody.appendChild(tr);
        });
        document.getElementById('cart-total').innerText = '₱' + total.toFixed(2);
    }

    function updateQty(index, qty) {
        const item = cart[index];
        if (qty > item.stock) {
            alert('Not enough stock!');
            renderCart(); // Reset
        } else {
            item.quantity = parseInt(qty);
            renderCart();
        }
    }

    document.getElementById('checkout-btn').addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Cart is empty!');
            return;
        }

        if (!confirm('Proceed with checkout?')) return;

        const data = {
            items: cart.map(item => ({ product_id: item.id, quantity: item.quantity }))
        };

        fetch('process_sale.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Sale successful! ID: ' + result.sale_id);
                cart = [];
                renderCart();
                document.getElementById('product-search').value = '';
                document.getElementById('product-results').innerHTML = '';
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(err => alert('Error processing sale.'));
    });
</script>

<?php require_once 'includes/footer.php'; ?>