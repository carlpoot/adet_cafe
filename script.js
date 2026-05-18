const FurrfectCafe = (() => {
  const STORAGE_KEYS = {
    cart: "furrfectcafe_cart",
    user: "furrfectcafe_user",
    orders: "furrfectcafe_orders"
  };

  const customerProtectedPages = [
    "index.php",
    "menu.php",
    "product.php",
    "cart.php",
    "checkout.php",
    "order-confirmation.php",
    "orders.php",
    "profile.php"
  ];

  const adminProtectedPages = [
    "admin-dashboard.php",
    "admin-orders.php",
    "admin-products.php",
    "admin-product-form.php"
  ];

  const categories = [
    { id: "all", name: "All" },
    { id: "hot-drinks", name: "Hot Drinks" },
    { id: "cold-drinks", name: "Cold Drinks" },
    { id: "pastries", name: "Pastries" },
    { id: "all-day-bites", name: "All-Day Bites" },
    { id: "fruit-blends", name: "Fruit Blends" }
  ];

  const products = [
    {
      id: 1,
      slug: "signature-cat-latte",
      name: "Signature Cat Latte",
      category: "hot-drinks",
      categoryLabel: "Hot Drinks",
      description: "Rich espresso with velvety steamed milk and adorable cat latte art.",
      price: 85,
      image: "https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=900&q=80",
      badge: "10% OFF",
      featured: true,
      bestseller: true
    },
    {
      id: 2,
      slug: "matcha-cloud",
      name: "Matcha Cloud",
      category: "cold-drinks",
      categoryLabel: "Cold Drinks",
      description: "Premium matcha blended with oat milk and soft cream cloud topping.",
      price: 110,
      image: "https://plus.unsplash.com/premium_photo-1663853489900-3f24ea776dea?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
      badge: "",
      featured: true,
      bestseller: true
    },
    {
      id: 3,
      slug: "matcha-cheesecake",
      name: "Matcha Cheesecake",
      category: "pastries",
      categoryLabel: "Pastries",
      description: "Creamy New York style cheesecake with a dreamy matcha finish.",
      price: 120,
      image: "https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?auto=format&fit=crop&w=900&q=80",
      badge: "Buy 2 Get 1",
      featured: true,
      bestseller: true
    },
    {
      id: 4,
      slug: "paw-print-cupcake",
      name: "Paw Print Cupcake",
      category: "pastries",
      categoryLabel: "Pastries",
      description: "Vanilla sponge with caramel buttercream and a cute paw-print topping.",
      price: 65,
      image: "https://images.unsplash.com/photo-1576618148400-f54bed99fcfd?auto=format&fit=crop&w=900&q=80",
      badge: "",
      featured: true,
      bestseller: false
    },
    {
      id: 5,
      slug: "brown-sugar-milk-tea",
      name: "Brown Sugar Milk Tea",
      category: "cold-drinks",
      categoryLabel: "Cold Drinks",
      description: "Tiger-striped brown sugar milk tea with fresh milk and tapioca pearls.",
      price: 95,
      image: "https://images.unsplash.com/photo-1558857563-b371033873b8?auto=format&fit=crop&w=900&q=80",
      badge: "",
      featured: false,
      bestseller: true
    },
    {
      id: 6,
      slug: "cafe-club-sandwich",
      name: "Cafe Club Sandwich",
      category: "all-day-bites",
      categoryLabel: "All-Day Bites",
      description: "Triple stack sandwich with chicken, egg, lettuce, and house mustard mayo.",
      price: 155,
      image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=900&q=80",
      badge: "New",
      featured: false,
      bestseller: true
    },
    {
      id: 7,
      slug: "mango-passion-cooler",
      name: "Mango Passion Cooler",
      category: "fruit-blends",
      categoryLabel: "Fruit Blends",
      description: "Fresh blended mango and passion fruit with a citrusy tropical lift.",
      price: 90,
      image: "https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=900&q=80",
      badge: "",
      featured: false,
      bestseller: true
    },
    {
      id: 8,
      slug: "caramel-macchiato",
      name: "Caramel Macchiato",
      category: "hot-drinks",
      categoryLabel: "Hot Drinks",
      description: "Layered espresso with silky milk, vanilla sweetness, and caramel drizzle.",
      price: 105,
      image: "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80",
      badge: "",
      featured: false,
      bestseller: true
    },
    {
      id: 9,
      slug: "iced-americano",
      name: "Iced Americano",
      category: "cold-drinks",
      categoryLabel: "Cold Drinks",
      description: "Bold double-shot espresso over ice for a clean and refreshing kick.",
      price: 75,
      image: "https://images.unsplash.com/photo-1709689242523-c6d8027c7499?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
      badge: "",
      featured: false,
      bestseller: false
    },
    {
      id: 10,
      slug: "croissant",
      name: "Croissant",
      category: "pastries",
      categoryLabel: "Pastries",
      description: "Buttery flaky French croissant baked fresh every morning.",
      price: 60,
      image: "https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=900&q=80",
      badge: "",
      featured: false,
      bestseller: false
    },
    {
      id: 11,
      slug: "strawberry-smoothie",
      name: "Strawberry Smoothie",
      category: "fruit-blends",
      categoryLabel: "Fruit Blends",
      description: "Creamy strawberry smoothie with yogurt and a hint of honey.",
      price: 95,
      image: "https://plus.unsplash.com/premium_photo-1726765809820-80c09e800f3b?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
      badge: "",
      featured: false,
      bestseller: false
    },
    {
      id: 12,
      slug: "chicken-waffle",
      name: "Chicken Waffle",
      category: "all-day-bites",
      categoryLabel: "All-Day Bites",
      description: "Crispy fried chicken on a golden waffle with maple spice glaze.",
      price: 175,
      image: "https://images.unsplash.com/photo-1600891963935-9e9544daf776?q=80&w=1171&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
      badge: "Best Seller",
      featured: false,
      bestseller: true
    }
  ];

  function getCurrentPage() {
    const page = window.location.pathname.split("/").pop();
    return page || "index.php";
  }

  function guardPages() {
    return true;
  }

  function getStorage(key, fallback) {
    try {
      const raw = localStorage.getItem(key);
      return raw ? JSON.parse(raw) : fallback;
    } catch (error) {
      console.error(`Error reading ${key}`, error);
      return fallback;
    }
  }

  function setStorage(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch (error) {
      console.error(`Error writing ${key}`, error);
    }
  }

  function formatPeso(amount) {
    return `₱${Number(amount).toFixed(2)}`;
  }

  function getCart() {
    return getStorage(STORAGE_KEYS.cart, []);
  }

  function saveCart(cart) {
    setStorage(STORAGE_KEYS.cart, cart);
    updateCartCountUI();
  }

  function getCartCount() {
    return getCart().reduce((total, item) => total + item.quantity, 0);
  }

  function getProductById(productId) {
    return products.find(product => product.id === productId);
  }

  function addToCart(productId, quantity = 1) {
    const product = getProductById(productId);
    if (!product) {
      alert("Product not found.");
      return;
    }

    const cart = getCart();
    const existing = cart.find(item => item.productId === productId);

    if (existing) {
      existing.quantity += quantity;
    } else {
      cart.push({
        productId,
        quantity,
        name: product.name,
        price: product.price,
        image: product.image,
        categoryLabel: product.categoryLabel
      });
    }

    saveCart(cart);
    showToast(`${product.name} added to cart`);
  }

  function removeFromCart(productId) {
    const updated = getCart().filter(item => item.productId !== productId);
    saveCart(updated);
  }

  function updateCartItemQuantity(productId, quantity) {
    const cart = getCart().map(item => {
      if (item.productId === productId) {
        return { ...item, quantity: Math.max(1, quantity) };
      }
      return item;
    });

    saveCart(cart);
  }

  function clearCart() {
    saveCart([]);
  }

  function getCartSubtotal() {
    return getCart().reduce((total, item) => total + item.price * item.quantity, 0);
  }

  function updateCartCountUI() {
    const count = getCartCount();

    document.querySelectorAll("[data-cart-count]").forEach(el => {
      el.textContent = count;
    });
  }

  function createBadgeHTML(product) {
    if (!product.badge) return "";

    return `
      <div class="product-badge-wrap">
        <span class="badge badge-accent">${product.badge}</span>
      </div>
    `;
  }

function createProductCard(product) {
  return `
    <article class="product-card card simple-menu-card">
      <a href="product.php?id=${product.id}" class="product-media" aria-label="View ${product.name}">
        ${createBadgeHTML(product)}
        <img src="${product.image}" alt="${product.name}">
      </a>

      <div class="product-body simple-menu-body">
        <h3 class="product-title">
          <a href="product.php?id=${product.id}">${product.name}</a>
        </h3>

        <div class="price">${formatPeso(product.price)}</div>
      </div>
    </article>
  `;
}

  function renderProductGrid(targetSelector, items) {
    const target = document.querySelector(targetSelector);
    if (!target) return;

    if (!items.length) {
      target.innerHTML = `
        <div class="empty-state">
          <h3>No items found</h3>
          <p class="section-text">Try another search keyword or category.</p>
        </div>
      `;
      return;
    }

    target.innerHTML = items.map(createProductCard).join("");
    bindAddToCartButtons();
  }

  function bindAddToCartButtons() {
    document.querySelectorAll(".add-to-cart-btn").forEach(button => {
      button.addEventListener("click", event => {
        event.preventDefault();
        event.stopPropagation();

        const productId = Number(button.dataset.productId);
        addToCart(productId, 1);
      });
    });
  }

  function renderFeaturedProducts(selector, count = 4) {
    const featuredItems = products.filter(product => product.featured).slice(0, count);
    renderProductGrid(selector, featuredItems);
  }

  function renderBestsellers(selector, count = 6) {
    const bestsellerItems = products.filter(product => product.bestseller).slice(0, count);
    renderProductGrid(selector, bestsellerItems);
  }

  function renderTodaysPicks(selector, count = 3) {
    const target = document.querySelector(selector);
    if (!target) return;

    const picks = products.filter(product => product.featured).slice(0, count);

    target.innerHTML = picks.map(product => `
      <div class="pick-item">
        <a href="product.php?id=${product.id}">
          <img src="${product.image}" alt="${product.name}">
        </a>

        <div>
          <strong>
            <a href="product.php?id=${product.id}">${product.name}</a>
          </strong>
          <span>${formatPeso(product.price)}</span>
        </div>

        <button 
          type="button" 
          class="add-to-cart-btn" 
          data-product-id="${product.id}">
          +
        </button>
      </div>
    `).join("");

    bindAddToCartButtons();
  }

  function renderCategoryCards(selector) {
    const target = document.querySelector(selector);
    if (!target) return;

    const iconMap = {
      "hot-drinks": "☕",
      "cold-drinks": "🥤",
      pastries: "🧁",
      "all-day-bites": "🍔",
      "fruit-blends": "🍹"
    };

    const items = categories
      .filter(category => category.id !== "all")
      .map(category => {
        const count = products.filter(product => product.category === category.id).length;

        return `
          <a href="menu.php?category=${category.id}" class="card category-card">
            <div class="category-icon">${iconMap[category.id] || "🍽️"}</div>
            <h3>${category.name}</h3>
            <div class="category-meta">${count} items</div>
          </a>
        `;
      });

    target.innerHTML = items.join("");
  }

  function renderMenuFilters(selector) {
    const target = document.querySelector(selector);
    if (!target) return;

    target.innerHTML = categories.map((category, index) => `
      <button 
        type="button"
        class="filter-chip ${index === 0 ? "active" : ""}" 
        data-category="${category.id}">
        ${category.name}
      </button>
    `).join("");
  }

  function initMenuPage() {
    const menuGrid = document.querySelector("#menuGrid");
    if (!menuGrid) return;

    const searchInput = document.querySelector("#menuSearch");
    const filterWrap = document.querySelector("#categoryFilters");
    const params = new URLSearchParams(window.location.search);

    let activeCategory = params.get("category") || "all";
    let activeSearch = params.get("search") || "";

    renderMenuFilters("#categoryFilters");

    if (searchInput) {
      searchInput.value = activeSearch;
    }

    function syncActiveChip() {
      filterWrap?.querySelectorAll(".filter-chip").forEach(item => {
        item.classList.toggle("active", item.dataset.category === activeCategory);
      });
    }

    function applyFilters() {
      const filtered = products.filter(product => {
        const matchesCategory = activeCategory === "all" || product.category === activeCategory;
        const searchText = `${product.name} ${product.categoryLabel} ${product.description}`.toLowerCase();
        const matchesSearch = searchText.includes(activeSearch.toLowerCase());

        return matchesCategory && matchesSearch;
      });

      renderProductGrid("#menuGrid", filtered);

      const countEl = document.querySelector("[data-menu-count]");
      if (countEl) countEl.textContent = filtered.length;

      syncActiveChip();
    }

    filterWrap?.addEventListener("click", event => {
      const chip = event.target.closest(".filter-chip");
      if (!chip) return;

      activeCategory = chip.dataset.category;
      applyFilters();
    });

    searchInput?.addEventListener("input", event => {
      activeSearch = event.target.value.trim();
      applyFilters();
    });

    applyFilters();
  }

  function showToast(message) {
    let toast = document.querySelector(".demo-toast");

    if (!toast) {
      toast = document.createElement("div");
      toast.className = "demo-toast";

      Object.assign(toast.style, {
        position: "fixed",
        right: "18px",
        bottom: "18px",
        zIndex: "9999",
        background: "#4b2617",
        color: "#fff",
        padding: "14px 18px",
        borderRadius: "16px",
        boxShadow: "0 12px 28px rgba(0,0,0,0.18)",
        fontFamily: "Arial, Helvetica, sans-serif",
        fontSize: "0.95rem",
        opacity: "0",
        transform: "translateY(10px)",
        transition: "all 0.25s ease"
      });

      document.body.appendChild(toast);
    }

    toast.textContent = message;

    requestAnimationFrame(() => {
      toast.style.opacity = "1";
      toast.style.transform = "translateY(0)";
    });

    clearTimeout(toast._hideTimer);

    toast._hideTimer = setTimeout(() => {
      toast.style.opacity = "0";
      toast.style.transform = "translateY(10px)";
    }, 1800);
  }

  function initMobileMenu() {
    const toggle = document.querySelector(".menu-toggle");
    const navLinks = document.querySelector(".nav-links");
    const navActions = document.querySelector(".nav-actions");

    if (!toggle || !navLinks || !navActions) return;

    toggle.addEventListener("click", () => {
      const expanded = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", String(!expanded));

      navLinks.classList.toggle("mobile-open");
      navActions.classList.toggle("mobile-open");

      if (!document.querySelector("#mobileMenuStyles")) {
        const style = document.createElement("style");
        style.id = "mobileMenuStyles";
        style.textContent = `
          @media (max-width: 820px) {
            .nav-links.mobile-open,
            .nav-actions.mobile-open {
              display: flex !important;
              position: absolute;
              left: 16px;
              right: 16px;
              background: #fff;
              border: 1px solid #e4d5c4;
              box-shadow: 0 16px 32px rgba(57, 30, 18, 0.10);
              border-radius: 20px;
              padding: 16px;
              flex-direction: column;
              align-items: stretch;
              gap: 14px;
            }

            .nav-links.mobile-open {
              top: 86px;
            }

            .nav-actions.mobile-open {
              top: 250px;
            }

            .nav-actions.mobile-open .btn,
            .nav-actions.mobile-open .cart-pill {
              display: inline-flex !important;
              width: 100%;
            }
          }
        `;

        document.head.appendChild(style);
      }
    });
  }

  function seedMockData() {
    if (!getStorage(STORAGE_KEYS.orders, null)) {
      setStorage(STORAGE_KEYS.orders, [
        {
          id: "FC-2026-0043",
          date: "Mar 27, 2026 2:14 PM",
          type: "Delivery",
          total: 387.5,
          paymentMethod: "Cash on Delivery",
          paymentStatus: "Unpaid",
          status: "Preparing",
          items: [
            { name: "Signature Cat Latte", quantity: 2, price: 85 },
            { name: "Matcha Cheesecake", quantity: 1, price: 120 },
            { name: "Paw Print Cupcake", quantity: 1, price: 65 }
          ]
        }
      ]);
    }
  }

  function initHomePage() {
    renderTodaysPicks("#todaysPicks", 3);
    renderCategoryCards("#homeCategories");
    renderBestsellers("#bestsellerGrid", 6);
    renderFeaturedProducts("#featuredGrid", 4);
  }

 function logoutCustomer() {
  const confirmLogout = confirm("Are you sure you want to log out of your customer account?");

  if (!confirmLogout) {
    return;
  }

  localStorage.removeItem("furrfectcafe_logged_in");
  window.location.href = "logout.php";
}

function logoutAdmin() {
  const confirmLogout = confirm("Are you sure you want to log out of the admin dashboard?");

  if (!confirmLogout) {
    return;
  }

  localStorage.removeItem("furrfectcafe_admin_logged_in");
  window.location.href = "logout.php";
}

  function initLogoutButtons() {
    document.querySelectorAll("[data-customer-logout]").forEach(button => {
      button.addEventListener("click", logoutCustomer);
    });

    document.querySelectorAll("[data-admin-logout]").forEach(button => {
      button.addEventListener("click", logoutAdmin);
    });
  }

  function initGlobal() {
    const allowed = guardPages();
    if (!allowed) return;

    seedMockData();
    updateCartCountUI();
    initMobileMenu();
    initHomePage();
    initMenuPage();
    initLogoutButtons();
  }

  return {
    products,
    categories,
    formatPeso,
    getCart,
    saveCart,
    addToCart,
    removeFromCart,
    updateCartItemQuantity,
    clearCart,
    getCartSubtotal,
    updateCartCountUI,
    renderProductGrid,
    renderFeaturedProducts,
    renderBestsellers,
    renderCategoryCards,
    logoutCustomer,
    logoutAdmin,
    initGlobal
  };
})();

window.FurrfectCafe = FurrfectCafe;

document.addEventListener("DOMContentLoaded", FurrfectCafe.initGlobal);