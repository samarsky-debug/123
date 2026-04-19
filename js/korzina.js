console.log('korzina.js loaded');

document.addEventListener('DOMContentLoaded', function() {
  (function() {
    // ========== ОПРЕДЕЛЯЕМ КОРНЕВУЮ ПАПКУ САЙТА ==========
    let rootPath = '';
    let pathname = window.location.pathname;
    // Убираем из пути всё после последнего слэша (если это файл)
    let pathWithoutFile = pathname.substring(0, pathname.lastIndexOf('/') + 1);
    // Если мы в папке /items/, то корень на уровень выше
    if (pathWithoutFile.includes('/items/')) {
        rootPath = pathWithoutFile.replace('/items/', '/');
    } else {
        rootPath = pathWithoutFile;
    }

    // Путь к cart_handler.php (в корне)
    let cartHandlerUrl = (pathWithoutFile.includes('/items/') ? '../' : '') + 'cart_handler.php';

    const cartIcon = document.getElementById('cartIconBtn');
    const cartOverlay = document.getElementById('cartOverlay');
    const cartPanel = document.getElementById('cartPanel');
    const closeCart = document.getElementById('closeCart');
    const cartItemsDiv = document.getElementById('cartItems');
    const cartTotalSpan = document.getElementById('cartTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');

    if (!cartIcon || !cartOverlay || !cartPanel) return;

    let cart = [];

    // Исправленная функция escapeHtml – работает с любыми типами
    function escapeHtml(str) {
      if (str === undefined || str === null) return '';
      // Принудительно преобразуем в строку
      str = String(str);
      return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
      });
    }

    // Загрузка корзины с сервера
    async function loadCart() {
      try {
        const response = await fetch(cartHandlerUrl + '?action=get');
        const data = await response.json();
        if (data.error) {
          console.error('Ошибка загрузки корзины:', data.error);
          return;
        }
        cart = data.items || [];
        renderCart();
        if (cartTotalSpan) {
          cartTotalSpan.textContent = (data.total || 0).toLocaleString() + ' ₽';
        }
      } catch (err) {
        console.error('Ошибка загрузки корзины:', err);
      }
    }

    // Добавление товара
    async function addToCart(productId, size, quantity = 1) {
      const formData = new FormData();
      formData.append('action', 'add');
      formData.append('id', productId);
      formData.append('size', size);
      formData.append('quantity', quantity);

      try {
        const response = await fetch(cartHandlerUrl, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.error) {
          console.error('Ошибка добавления:', result.error);
          return false;
        }
        await loadCart();
        return true;
      } catch (err) {
        console.error('Ошибка добавления:', err);
        return false;
      }
    }

    // Обновление количества
    async function updateQuantity(productId, size, delta) {
      const formData = new FormData();
      formData.append('action', 'update');
      formData.append('id', productId);
      formData.append('size', size);
      formData.append('delta', delta);

      try {
        const response = await fetch(cartHandlerUrl, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.error) {
          console.error('Ошибка обновления:', result.error);
          return;
        }
        await loadCart();
      } catch (err) {
        console.error('Ошибка обновления:', err);
      }
    }

    // Удаление товара
    async function removeItem(productId, size) {
      const formData = new FormData();
      formData.append('action', 'remove');
      formData.append('id', productId);
      formData.append('size', size);

      try {
        const response = await fetch(cartHandlerUrl, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.error) {
          console.error('Ошибка удаления:', result.error);
          return;
        }
        await loadCart();
      } catch (err) {
        console.error('Ошибка удаления:', err);
      }
    }

    // Отрисовка корзины
    function renderCart() {
      if (!cartItemsDiv || !cartTotalSpan) return;

      if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p class="empty-cart">Корзина пуста</p>';
        cartTotalSpan.textContent = '0 ₽';
        return;
      }

      let html = '';
      let totalSum = 0;
      cart.forEach(item => {
        totalSum += item.price * item.quantity;
        // Принудительно преобразуем name и size в строки перед передачей в escapeHtml
        const itemName = String(item.name);
        const itemSize = String(item.size);
        html += `
          <div class="cart-item" data-id="${item.id}" data-size="${item.size}">
            <img src="${item.image}" alt="${itemName}" class="cart-item-img" onerror="this.src='img/placeholder.jpg'">
            <div class="cart-item-info">
              <div class="cart-item-title">${escapeHtml(itemName)}</div>
              <div class="cart-item-size">Размер: ${escapeHtml(itemSize)}</div>
              <div class="cart-item-price">${item.price.toLocaleString()} ₽</div>
              <div class="cart-item-actions">
                <button class="decrease-qty">−</button>
                <span>${item.quantity}</span>
                <button class="increase-qty">+</button>
                <span class="remove-item" title="Удалить">Удалить</span>
              </div>
            </div>
          </div>
        `;
      });
      cartItemsDiv.innerHTML = html;
      cartTotalSpan.textContent = `${totalSum.toLocaleString()} ₽`;

      document.querySelectorAll('.decrease-qty').forEach(btn => {
        btn.removeEventListener('click', handleDecrease);
        btn.addEventListener('click', handleDecrease);
      });
      document.querySelectorAll('.increase-qty').forEach(btn => {
        btn.removeEventListener('click', handleIncrease);
        btn.addEventListener('click', handleIncrease);
      });
      document.querySelectorAll('.remove-item').forEach(btn => {
        btn.removeEventListener('click', handleRemove);
        btn.addEventListener('click', handleRemove);
      });
    }

    function handleDecrease(e) {
      const cartItemDiv = e.target.closest('.cart-item');
      if (cartItemDiv) {
        const id = cartItemDiv.dataset.id;
        const size = cartItemDiv.dataset.size;
        updateQuantity(id, size, -1);
      }
    }
    function handleIncrease(e) {
      const cartItemDiv = e.target.closest('.cart-item');
      if (cartItemDiv) {
        const id = cartItemDiv.dataset.id;
        const size = cartItemDiv.dataset.size;
        updateQuantity(id, size, 1);
      }
    }
    function handleRemove(e) {
      const cartItemDiv = e.target.closest('.cart-item');
      if (cartItemDiv) {
        const id = cartItemDiv.dataset.id;
        const size = cartItemDiv.dataset.size;
        removeItem(id, size);
      }
    }

    function openCart() {
      cartOverlay.classList.add('active');
      cartPanel.classList.add('active');
      document.body.style.overflow = 'hidden';
      loadCart();
    }

    function closeCartPanel() {
      cartOverlay.classList.remove('active');
      cartPanel.classList.remove('active');
      document.body.style.overflow = '';
    }

    cartIcon.addEventListener('click', openCart);
    closeCart.addEventListener('click', closeCartPanel);
    cartOverlay.addEventListener('click', closeCartPanel);

    // ========== КНОПКА ОФОРМЛЕНИЯ ЗАКАЗА ==========
    if (checkoutBtn) {
      checkoutBtn.addEventListener('click', () => {
        if (cart.length === 0) {
          alert('Ваша корзина пуста');
          return;
        }
        window.location.href = rootPath + 'checkout.php';
      });
    }

    // Обработка выбора размера на странице товара
    const sizeBadges = document.querySelectorAll('.size-badge');
    sizeBadges.forEach(badge => {
      badge.addEventListener('click', function() {
        sizeBadges.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
      });
    });

    // Добавление в корзину через кнопку .add-to-cart-btn
    document.body.addEventListener('click', async (e) => {
      const addBtn = e.target.closest('.add-to-cart, .add-to-cart-btn');
      if (!addBtn) return;
      e.preventDefault();

      const productContainer = addBtn.closest('.description-section');
      if (!productContainer) {
        console.warn('Не найден контейнер .description-section');
        return;
      }

      const productId = productContainer.dataset.id;
      if (!productId) {
        console.warn('Нет data-id у контейнера');
        return;
      }

      let selectedSize = null;
      const activeSize = productContainer.querySelector('.size-badge.active');
      if (activeSize) {
        selectedSize = activeSize.innerText.trim();
      } else {
        const anySize = productContainer.querySelector('.size-badge');
        if (anySize) {
          alert('Пожалуйста, выберите размер');
          return;
        }
        selectedSize = 'One size';
      }

      const success = await addToCart(productId, selectedSize, 1);
      if (success) {
        const originalText = addBtn.innerText;
        addBtn.innerText = '✓ Добавлено';
        setTimeout(() => { addBtn.innerText = originalText; }, 800);
        openCart();
      } else {
        alert('Не удалось добавить товар');
      }
    });

    loadCart();
  })();
});