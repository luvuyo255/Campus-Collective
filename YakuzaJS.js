// Function to check internet connectivity
function connStatus() {
    if (navigator.onLine == true) {
        window.alert("You have good connectivity.");
    } else {
        window.alert("You do not have good connectivity.");
    }
}

// Function to show developer/browser info
function DevInfo() {
    let cookies;
    if (navigator.cookieEnabled == true) {
        cookies = "COOKIES ENABLED!";
    } else {
        cookies = "COOKIES NOT ENABLED!";
    }
    window.alert(
        "APP CODE NAME => " + navigator.appCodeName +
        "\nAPP NAME => " + navigator.appName +
        "\nAPP VERSION => " + navigator.appVersion +
        "\nCOOKIES ENABLED => " + cookies
    );
}

// Slideshow functionality
document.addEventListener("DOMContentLoaded", function() {

    // Array of banner image paths
    const banners = [
        "PICTURES/banner1.jpg",
        "PICTURES/banner2.jpg",
        "PICTURES/banner3.jpg",
        "PICTURES/banner4.jpg"
    ];

    let currentBanner = 0; // Index of the currently displayed banner

    // Get the <img> element by ID
    const bannerElement = document.getElementById("Sale1");

    // Safety check: make sure the element exists
    if (!bannerElement) {
        console.error("Banner element with ID 'Sale1' not found!");
        return; // Stop execution if element is missing
    }

    // Function to show the next banner in the array
    function showNextBanner() {
        currentBanner++; // Move to the next image
        if (currentBanner >= banners.length) {
            currentBanner = 0; // Loop back to the first image
        }
        bannerElement.src = banners[currentBanner]; // Update the <img> src
    }

    // Automatically change banner every 3 seconds
    setInterval(showNextBanner, 2000);

});

// Using DOM properties to save form data to local storage for display on buy page
document.addEventListener("DOMContentLoaded", () => {
    const sell_form = document.getElementById("sell-form");
  
    sell_form.addEventListener("submit", function () {
      
      // Extracting values from form fields
      let name = document.getElementById("prod-name").value;
      let type = document.getElementById("prod-type").value;
      let desc = document.getElementById("prod-desc").value;
      let price = document.getElementById("prod-price").value;
  
      // storing each field directly in local storage
      localStorage.setItem("prod-name", name);
      localStorage.setItem("prod-type", type);
      localStorage.setItem("prod-desc", desc);
      localStorage.setItem("prod-price", price);
  
  
    });
  });

// Displaying the saved product on the buy page
document.addEventListener("DOMContentLoaded", function() {
    // Only display on the buy page
    if (window.location.pathname.includes("buy.html")) {
        // Grab the container where products are displayed
        const productContainer = document.querySelector(".random_products");

        // Get the values saved in localStorage from the sell form
        const name = localStorage.getItem("prod-name");
        const type = localStorage.getItem("prod-type");
        const desc = localStorage.getItem("prod-desc");
        const price = localStorage.getItem("prod-price");
        const seller = localStorage.getItem("prod-seller") || "The Random Shop";

        // If we have a saved product, display it
        if (name && type && desc && price) {
            const newCard = document.createElement("div");
            newCard.classList.add("item-card");

            // Placeholder image for now 
            const imgSrc = localStorage.getItem("prod-pic") || "PICTURES/placeholder.jpg";

            // Build product card
            newCard.innerHTML = `
            <img src="${imgSrc}" alt="${name}" class="item-pic">
            <h3>${name}</h3>
            <p>The Random Shop</p>
            <p>R${price}</p>
            <button onclick="addToCart('${name}', ${price}, '${imgSrc}', '${type}')">Buy</button>
            `;

            // Add it to the products section
            productContainer.appendChild(newCard);

            // Clear localStorage so it doesn’t keep duplicating
            localStorage.removeItem("prod-name");
            localStorage.removeItem("prod-type");
            localStorage.removeItem("prod-desc");
            localStorage.removeItem("prod-price");
        }
    }
});

document.addEventListener("DOMContentLoaded", function() {

   // Home Page search functionality
  if (window.location.pathname.includes("home.html")) {
    const searchInput = document.querySelector(".search input");
    const searchBtn = document.querySelector(".search button");

    searchBtn.addEventListener("click", function() {
      const query = searchInput.value.trim();
      if (query) {
        window.location.href = `buy.html?search=${encodeURIComponent(query)}`;
      }
    });

    searchInput.addEventListener("keypress", function(e) {
      if (e.key === "Enter") searchBtn.click();
    });
  }

  // The buy page search functionality
    if (window.location.pathname.includes("buy.html")) {
        const searchInput = document.querySelector(".search input");
        const searchBtn = document.querySelector(".search button"); 
        const productCards = document.querySelectorAll(".item-card"); 

        // Filter function
        const filterProducts = (query) => {
            query = query.toLowerCase();
            productCards.forEach(card => {
            const name = card.querySelector("h3").textContent.toLowerCase();
            const seller = card.querySelector("p").textContent.toLowerCase();

            if (name.includes(query) || seller.includes(query)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
            });
        };

        // Apply search query from URL from the home page if present
        const urlParams = new URLSearchParams(window.location.search);
        const initialQuery = urlParams.get("search");
        if (initialQuery) {
            searchInput.value = initialQuery;
            filterProducts(initialQuery);

              // Remove the query from the URL so refreshing resets the filter
            window.history.replaceState({}, document.title, window.location.pathname);

        }

        // Display filtered items when search button is clicked
        searchBtn.addEventListener("click", function() {
            const query = searchInput.value.trim();
            filterProducts(query);
        });

        // Filter on Enter key
        searchInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") searchBtn.click();
        });
    }

});

// FIlter functionality on the buy page
document.addEventListener('DOMContentLoaded', () => {
    const filterBtns = document.querySelectorAll('.filters .filter-btn');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.textContent;

            // grab product cards at the time of click
            const products = document.querySelectorAll('.item-card');

            products.forEach(product => {
                if (filter === 'All' || product.dataset.prodtype === filter) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        });
    });
});




//sHOPING AND CART
// Add to Cart with product_id and quantity
function addToCart(name, price, image, seller, product_id) {
    console.log("Adding to cart:", name, price, seller, product_id);
    const parsedPrice = parseFloat(price);
    if (isNaN(parsedPrice)) {
        console.error("Invalid price:", price);
        alert("Error: Invalid price for the item.");
        return;
    }
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    // Check if item exists, increment quantity
    const existingItem = cart.find(item => item.product_id === parseInt(product_id));
    if (existingItem) {
        existingItem.quantity = (existingItem.quantity || 1) + 1;
    } else {
        cart.push({ 
            name: name, 
            price: parsedPrice,
            image: image, 
            seller: seller,
            product_id: parseInt(product_id),
            quantity: 1
        });
    }
    localStorage.setItem("cart", JSON.stringify(cart));
    console.log("Cart after adding:", cart);
    let proceed = window.confirm("Do you want to proceed to Cart?");
    if (proceed) {
        window.location.href = "cart.html";
    }
}

// Render Cart with checkout buttons
function renderCart() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let cartSection = document.getElementById("cartSection");
    let emptyMessage = document.getElementById("emptyMessage");
    if (!cartSection || !emptyMessage) return;

    cartSection.innerHTML = "";
    if (cart.length === 0) {
        emptyMessage.style.display = "block";
        cartSection.style.display = "none";
        return;
    }
    emptyMessage.style.display = "none";
    cartSection.style.display = "block";

    // Group by seller
    let groupedCart = {};
    cart.forEach((item, index) => {
        if (!groupedCart[item.seller]) groupedCart[item.seller] = [];
        groupedCart[item.seller].push({ ...item, index });
    });

    for (let seller in groupedCart) {
        let total = 0;
        let shopDiv = document.createElement("div");
        shopDiv.classList.add("shop-section");

        let itemsHTML = "";
        groupedCart[seller].forEach(item => {
            total += item.price * (item.quantity || 1);
            itemsHTML += `
                <div class="cart-item">
                    <img src="${item.image}" alt="${item.name}">
                    <div class="item-info">
                        <p><strong>${item.name}</strong> x${item.quantity || 1}</p>
                        <p>Price: R${(item.price * (item.quantity || 1)).toFixed(2)}</p>
                    </div>
                    <button class="remove-btn" onclick="removeFromCart(${item.index})">
                        <i class="fa fa-trash"></i>
                    </button>
                    <button onclick="proceedCheckout(${item.product_id})">Checkout</button>
                </div>
            `;
        });

        let totalHTML = `<div class="checkout"><p><strong>Total for ${seller}: R${total.toFixed(2)}</strong></p></div>`;
        shopDiv.innerHTML = `<h2>Shop: ${seller}</h2>` + itemsHTML + totalHTML;
        cartSection.appendChild(shopDiv);
    }
}

// Proceed to Checkout
function proceedCheckout(product_id) {
    console.log("Proceeding to checkout for product_id:", product_id);
    window.location.href = `checkout.php?id=${encodeURIComponent(product_id)}`;
}

// Remove from Cart
function removeFromCart(index) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    renderCart();
}

// Initialize cart rendering
document.addEventListener("DOMContentLoaded", () => {
    if (window.location.pathname.includes("cart.html")) {
        renderCart();
    }
});

renderCart();     
/*Create Password Validation*/
function passwordTime(){
    
      const passwordInput = document.getElementById("password");
      const strengthMsg = document.getElementById("PwdStren");
      

      function checkPasswordStrength(password) {
        let strength = 0;

        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        return strength;
      }

      passwordInput.addEventListener('input', function () {
        const strength = checkPasswordStrength(passwordInput.value);

        if (strength <= 2) {
          strengthMsg.textContent = "Weak password (use upper/lowercase, numbers & symbols)";
          strengthMsg.className = "weak";
        } else if (strength === 3 || strength === 4) {
          strengthMsg.textContent = "Medium strength password";
          strengthMsg.className = "medium"; //for styling purposes
        } else {
          strengthMsg.textContent = "Strong password ✔";
          strengthMsg.className = "strong"; //for styling purposes
        }
      });

      // Prevent form submission if password is weak
      document.getElementById("registerForm").addEventListener("submit", function(e) {
        const strength = checkPasswordStrength(passwordInput.value);
        if (strength < 4) { // require at least medium or more strength
          e.preventDefault();
          alert(" Please choose a stronger password before submitting!");
        }
      });


    }

    /* checks if password matches confirm password */

function passwordConfirm(){
  const confirmPass = document.getElementById("confirm_password"); 
  const passwordInput = document.getElementById("password");
  const pwdcompare = document.getElementById("confpwd");
  let checker = 0;

  function compareTime(password1, password2){ /* Helper function */
    
  if(password1 == password2){
    pwdcompare.textContent = "Password Match!";
    pwdcompare.className = "strong";
    checker = checker + 2;
  }else{
    pwdcompare.textContent = "Password Does Not Match!"
    pwdcompare.className = "weak";
    checker = checker - 2;
  }
  return checker;
}
document.getElementById("registerForm").addEventListener("submit", function(e) {
  const checked = compareTime(passwordInput, confirmPass);
  if(checked == -2){
    e.preventDefault();
    alert("Please ensure that passwords match before submitting!");
  }
});
confirmPass.addEventListener('input', function () { //makes sure that this is updated live as the user inputs their password confirmation.
  compareTime(passwordInput.value, confirmPass.value);
});

      }
 
/*
sellForm.addEventListener("submit", function (e) {
    const category = categorySelect.value;

    // Validate Service category
    if (category === "Service") {
        if (!serviceAvailabilitySelect.value) {
            e.preventDefault();
            alert("Please select service availability.");
            serviceAvailabilitySelect.focus();
            return;
        }
    } 
    // Validate Product categories
    else if (category && category !== "Service") {
        const quantity = parseInt(prodQuantityInput.value, 10);
        if (isNaN(quantity) || quantity < 1) {
            e.preventDefault();
            alert("Please enter a quantity of at least 1.");
            prodQuantityInput.focus();
            return;
        }

        if (!prodConditionSelect.value) {
            e.preventDefault();
            alert("Please select a product condition.");
            prodConditionSelect.focus();
            return;
        }
    }
});
*/

/* Logic for clicking on item card to view more details 
document.querySelectorAll('.item-card').forEach(card => {
  card.addEventListener('click', event => {
    // If the user clicked the Buy button, stop here
    if (event.target.classList.contains('buy-btn')) return;

    // Otherwise, go to the details page
    const id = card.dataset.id; // from data-id attribute
    window.location.href = `prod_details.html?id=${id}`;
  });
});
*/

/* Logic for displaying item details on the item-details page 
document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.random_products');
  if (!container) return;

  // Make cards feel clickable
  container.querySelectorAll('.item-card').forEach(c => c.style.cursor = 'pointer');

  container.addEventListener('click', (e) => {
    // find the card that was clicked (or null)
    const card = e.target.closest('.item-card');
    if (!card) return;

    // If the click was inside a button, let the button handle it (addToCart etc.)
    if (e.target.closest('button')) return;

    // Build an id/slug from a data-id or from the <h3> title
    let id = card.dataset.id;
    if (!id) {
      const title = card.querySelector('h3')?.textContent.trim() || 'item';
      id = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
      card.dataset.id = id; // cache it
    }

    // Redirect to details page (example)
    window.location.href = `prod_details.php?id=${encodeURIComponent(id)}`;
  });
});
*/

document.addEventListener("DOMContentLoaded", () => {
  const password = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');

  const confirmPassword = document.getElementById('confirm_password');
  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

  if(togglePassword && password){
      togglePassword.addEventListener('mousedown', () => { password.type = 'text'; });
      togglePassword.addEventListener('mouseup', () => { password.type = 'password'; });
      togglePassword.addEventListener('mouseout', () => { password.type = 'password'; });
  }

  if(toggleConfirmPassword && confirmPassword){
      toggleConfirmPassword.addEventListener('mousedown', () => { confirmPassword.type = 'text'; });
      toggleConfirmPassword.addEventListener('mouseup', () => { confirmPassword.type = 'password'; });
      toggleConfirmPassword.addEventListener('mouseout', () => { confirmPassword.type = 'password'; });
  }
});

// Example to fetch cart items from localStorage and display
    const orderItems = document.getElementById("orderItems");
    const subtotalEl = document.getElementById("subtotal");
    const totalEl = document.getElementById("total");
    const shippingCost = 50;

    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length > 0) {
      orderItems.innerHTML = "";
      let subtotal = 0;

      cart.forEach(item => {
        subtotal += item.price * item.quantity;
        orderItems.innerHTML += `
          <div class="order-item">
            <p>${item.name} x${item.quantity}</p>
            <p>R${(item.price * item.quantity).toFixed(2)}</p>
          </div>`;
      });

      subtotalEl.textContent = `R${subtotal.toFixed(2)}`;
      totalEl.textContent = `R${(subtotal + shippingCost).toFixed(2)}`;
    }


  // grab cart info from local storage or start empty
//let cart = JSON.parse(localStorage.getItem("cart")) || [];

// show or hide stuff depending on if cart has items
function updateCartDisplay() {
  const emptyMessage = document.getElementById("emptyMessage");
  const cartSection = document.getElementById("cartSection");
  const checkoutSection = document.getElementById("checkoutSection");

  if (cart.length === 0) {
    emptyMessage.style.display = "block";
    cartSection.style.display = "none";
    checkoutSection.style.display = "none";
  } else {
    emptyMessage.style.display = "none";
    cartSection.style.display = "block";
    checkoutSection.style.display = "block";
  }
}

// when checkout button clicked, move to checkout2 page
document.addEventListener("DOMContentLoaded", () => {
  const checkoutBtn = document.getElementById("checkoutBtn");
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", () => {
      if (cart.length > 0) {
        window.location.href = "checkout2.html";
      } else {
        alert("Your cart is empty!");
      }
    });
  }
});

function goToProduct(id) {
  window.location.href = `prod_details.php?id=${id}`;
}

/*
document.getElementById('sell-form').addEventListener('submit', function(e) {
    const quantityInput = document.getElementById('prod-quantity');
    const quantity = parseInt(quantityInput.value, 10);

    if (isNaN(quantity) || quantity < 1) {
        e.preventDefault(); // Stop form submission
        alert("Please enter a quantity of at least 1.");
        quantityInput.focus();
    }
});
*/

// dashboard
//document.getElementById("registerForm" || "resetForm").addEventListener("submit", function(e) {
  // 
//});

const registerForm = document.getElementById("registerForm");
const resetForm = document.getElementById("resetForm");

if (registerForm) {
  registerForm.addEventListener("submit", function(e) {
    // 
  });
}

if (resetForm) {
  resetForm.addEventListener("submit", function(e) {
    // 
  });
}

// Dynamically changes the product details page based on the category of the listing
document.addEventListener("DOMContentLoaded", () => {
    const sellForm = document.getElementById("sell-form");
    const categorySelect = document.getElementById("prod-type");
    const prodQuantityInput = document.getElementById("prod-quantity");
    const prodConditionSelect = document.getElementById("prod-condition");
    const serviceAvailabilitySelect = document.getElementById("service-availability");
    const prodQuantityRow = document.getElementById("prod-quantity-row");
    const prodConditionRow = document.getElementById("prod-condition-row");
    const serviceAvailabilityRow = document.getElementById("service-availability-row");

    // Show/hide fields based on category
    function updateFormFields() {
        if (categorySelect.value === "Service") {
            prodQuantityRow.style.display = "none";
            prodConditionRow.style.display = "none";
            serviceAvailabilityRow.style.display = "block";
            prodQuantityInput.required = false;
            prodConditionSelect.required = false;
            serviceAvailabilitySelect.required = true;
        } else if (categorySelect.value) {
            prodQuantityRow.style.display = "block";
            prodConditionRow.style.display = "block";
            serviceAvailabilityRow.style.display = "none";
            prodQuantityInput.required = true;
            prodConditionSelect.required = true;
            serviceAvailabilitySelect.required = false;
        } else {
            prodQuantityRow.style.display = "none";
            prodConditionRow.style.display = "none";
            serviceAvailabilityRow.style.display = "none";
            prodQuantityInput.required = false;
            prodConditionSelect.required = false;
            serviceAvailabilitySelect.required = false;
        }
    }

    // Run on page load
    updateFormFields();

    // Run whenever category changes
    categorySelect.addEventListener("change", updateFormFields);

    // Form validation on submit
    sellForm.addEventListener("submit", function (e) {
        const category = categorySelect.value;

        if (category === "Service") {
            if (!serviceAvailabilitySelect.value) {
                e.preventDefault();
                alert("Please select service availability.");
                serviceAvailabilitySelect.focus();
                return;
            }
        } else if (category && category !== "Service") {
            const quantity = parseInt(prodQuantityInput.value, 10);
            if (isNaN(quantity) || quantity < 1) {
                e.preventDefault();
                alert("Please enter a quantity of at least 1.");
                prodQuantityInput.focus();
                return;
            }

            if (!prodConditionSelect.value) {
                e.preventDefault();
                alert("Please select a product condition.");
                prodConditionSelect.focus();
                return;
            }
        }
    });
});






/*
// This dynamically changes the sell form based on category selection 
document.addEventListener("DOMContentLoaded", () => {
    const categorySelect = document.getElementById("prod-type");
    const prodQuantityRow = document.getElementById("prod-quantity-row");
    const prodConditionRow = document.getElementById("prod-condition-row");
    const serviceAvailabilityRow = document.getElementById("service-availability-row");

    const prodQuantityInput = document.getElementById("prod-quantity");
    const prodConditionSelect = document.getElementById("prod-condition");
    const serviceAvailabilitySelect = document.getElementById("service-availability");

    function updateFormFields() {
        if (categorySelect.value === "Service") {
            serviceAvailabilityRow.style.display = "block";
            prodQuantityRow.style.display = "none";
            prodConditionRow.style.display = "none";

            serviceAvailabilitySelect.required = true;
            prodQuantityInput.required = false;
            prodConditionSelect.required = false;
        } else if (categorySelect.value) {
            serviceAvailabilityRow.style.display = "none";
            prodQuantityRow.style.display = "block";
            prodConditionRow.style.display = "block";

            serviceAvailabilitySelect.required = false;
            prodQuantityInput.required = true;
            prodConditionSelect.required = true;
        } else {
            // nothing selected
            serviceAvailabilityRow.style.display = "none";
            prodQuantityRow.style.display = "none";
            prodConditionRow.style.display = "none";

            serviceAvailabilitySelect.required = false;
            prodQuantityInput.required = false;
            prodConditionSelect.required = false;
        }
    }

    // Make sure fields are hidden initially (CSS ensures this)
    updateFormFields();

    // When the category changes
    categorySelect.addEventListener("change", updateFormFields);

    // Validation on submit
    const sellForm = document.getElementById("sell-form");
    sellForm.addEventListener("submit", function (e) {
        if (categorySelect.value !== "Service") {
            if (!prodQuantityInput.value || prodQuantityInput.value < 1) {
                e.preventDefault();
                alert("Please enter a quantity of at least 1.");
                prodQuantityInput.focus();
                return;
            }
            if (!prodConditionSelect.value) {
                e.preventDefault();
                alert("Please select a product condition.");
                prodConditionSelect.focus();
                return;
            }
        }
    });
});
*/




/*
// On page load, set initial visibility based on current category
document.addEventListener("DOMContentLoaded", () => {
  const categoryInput = document.getElementById("prod-type");
  const quantityRow = document.getElementById("quantityRow");
  const availabilityRow = document.getElementById("availabilityRow");

  if (!categoryInput || !quantityRow || !availabilityRow) return;

  const category = categoryInput.value.trim();

  if (category === "Service") {
    availabilityRow.style.display = "block";
    quantityRow.style.display = "none";
  } else {
    availabilityRow.style.display = "none";
    quantityRow.style.display = "block";
  }
});
*/ 
