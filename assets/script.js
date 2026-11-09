/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Unified Client-Side Application Script (Q3 & Q6 Vanilla JS)
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    /**
     * Context-aware HTML escaping helper for dynamic DOM rendering
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    /**
     * Shared Toast Notification Trigger for Bootstrap 5 & fallback
     */
    function showToastNotification(type, message) {
        const toastEl = document.getElementById('catalogToast');
        const toastMsg = document.getElementById('catalogToastMessage');
        const toastIcon = document.getElementById('catalogToastIcon');
        if (!toastEl || !toastMsg) return;

        toastMsg.textContent = message;
        if (type === 'danger') {
            toastEl.classList.remove('text-bg-success', 'text-bg-warning');
            toastEl.classList.add('text-bg-danger');
            if (toastIcon) toastIcon.className = 'bi bi-exclamation-circle-fill fs-5';
        } else if (type === 'warning') {
            toastEl.classList.remove('text-bg-success', 'text-bg-danger');
            toastEl.classList.add('text-bg-warning');
            if (toastIcon) toastIcon.className = 'bi bi-exclamation-triangle-fill fs-5';
        } else {
            toastEl.classList.remove('text-bg-danger', 'text-bg-warning');
            toastEl.classList.add('text-bg-success');
            if (toastIcon) toastIcon.className = 'bi bi-check-circle-fill fs-5';
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 }).show();
        } else {
            toastEl.classList.add('show');
            setTimeout(() => toastEl.classList.remove('show'), 3500);
        }
    }

    // ==========================================
    // 1. Mobile Sidebar & Global UI
    // ==========================================
    const sidebar = document.querySelector('.app-sidebar');
    const toggleBtn = document.querySelector('[data-sidebar-toggle]');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-open');
        });
    }

    const backdrop = document.querySelector('.sidebar-backdrop');
    if (backdrop) {
        backdrop.addEventListener('click', () => {
            document.body.classList.remove('sidebar-open');
        });
    }

    // ==========================================
    // 2. Point of Sale (POS) Counter Calculator
    // ==========================================
    const saleRows = document.getElementById('saleRows');
    const saleTemplate = document.getElementById('saleRowTemplate');
    const discountInput = document.getElementById('discountInput');
    const saleForm = document.getElementById('saleForm');

    if (saleRows && saleTemplate && discountInput) {
        const formatMoney = n => '৳' + (Number(n) || 0).toFixed(2);

        const calculateRow = row => {
            const select = row.querySelector('.medicine-select');
            const opt = select?.selectedOptions[0];
            const price = Number(opt?.dataset?.price || 0);
            const stock = Number(opt?.dataset?.stock || 0);
            const qtyInput = row.querySelector('.quantity-input');
            const qty = Math.max(0, Number(qtyInput?.value || 0));

            const stockCell = row.querySelector('.stock-cell');
            const priceCell = row.querySelector('.price-cell');
            const totalCell = row.querySelector('.line-total');

            if (stockCell) stockCell.textContent = select?.value ? String(stock) : '—';
            if (priceCell) priceCell.textContent = formatMoney(price);
            if (totalCell) totalCell.textContent = formatMoney(price * qty);

            if (qtyInput) {
                qtyInput.setCustomValidity(select?.value && qty > stock ? 'Quantity exceeds stock' : '');
            }

            row.dataset.total = String(price * qty);
            calculateTotals();
        };

        const calculateTotals = () => {
            const allRows = [...saleRows.querySelectorAll('.sale-row')];
            const subtotal = allRows.reduce((sum, r) => sum + Number(r.dataset.total || 0), 0);
            const discountVal = Math.max(0, Number(discountInput.value || 0));
            const grandTotal = Math.max(0, subtotal - discountVal);

            const subtotalText = document.getElementById('subtotalText');
            const grandTotalText = document.getElementById('grandTotalText');

            if (subtotalText) subtotalText.textContent = formatMoney(subtotal);
            if (grandTotalText) grandTotalText.textContent = formatMoney(grandTotal);

            discountInput.setCustomValidity(discountVal > subtotal ? 'Discount exceeds subtotal' : '');
        };

        const addRow = () => {
            const clone = saleTemplate.content.cloneNode(true);
            const row = clone.querySelector('.sale-row');

            row.querySelector('.medicine-select')?.addEventListener('change', () => calculateRow(row));
            row.querySelector('.quantity-input')?.addEventListener('input', () => calculateRow(row));
            row.querySelector('.remove-row')?.addEventListener('click', () => {
                if (saleRows.children.length > 1) {
                    row.remove();
                } else {
                    const sel = row.querySelector('.medicine-select');
                    const qi = row.querySelector('.quantity-input');
                    if (sel) sel.value = '';
                    if (qi) qi.value = 1;
                    calculateRow(row);
                }
                calculateTotals();
            });

            saleRows.appendChild(clone);
            calculateRow(row);
        };

        document.getElementById('addRow')?.addEventListener('click', () => {
            if (saleRows.children.length < 40) addRow();
        });

        discountInput.addEventListener('input', calculateTotals);

        if (saleForm) {
            saleForm.addEventListener('submit', event => {
                const qtyById = new Map();
                for (const row of saleRows.children) {
                    const sel = row.querySelector('.medicine-select');
                    const qi = row.querySelector('.quantity-input');
                    const id = sel?.value;
                    if (!id) continue;
                    const count = Number(qi?.value || 0);
                    qtyById.set(id, (qtyById.get(id) || 0) + count);
                    const stock = Number(sel.selectedOptions[0]?.dataset.stock || 0);
                    if (qtyById.get(id) > stock) {
                        event.preventDefault();
                        alert('Insufficient stock for one or more selected items.');
                        return;
                    }
                }
            });
        }

        addRow();
    }

    // ==========================================
    // 3. Storefront Catalog Live Search & Filter
    // ==========================================
    const searchInput = document.getElementById('catalogLiveSearch') || document.getElementById('catalog_keyword');
    const categorySelect = document.getElementById('catalogCategorySelect') || document.getElementById('catalog_cat');
    const catalogGrid = document.getElementById('catalogMedicineGrid');

    if (searchInput && catalogGrid) {
        let debounceTimer = null;
        let activeSearchController = null;

        const showSearchError = message => {
            catalogGrid.innerHTML = `
                <div class="col-12 py-4">
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>${escapeHtml(message)}</div>
                    </div>
                </div>`;
        };

        const performSearch = () => {
            if (activeSearchController) {
                activeSearchController.abort();
            }
            activeSearchController = new AbortController();

            // Display loading spinner
            catalogGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Loading medicines...</span>
                    </div>
                    <p class="text-secondary mt-3 small fw-semibold">Searching medicine inventory...</p>
                </div>`;

            const query = encodeURIComponent(searchInput.value.trim());
            const catId = categorySelect ? encodeURIComponent(categorySelect.value) : '';
            const url = `api/router.php?action=search&q=${query}&category_id=${catId}`;

            fetch(url, {
                signal: activeSearchController.signal,
                headers: { 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`Server returned HTTP ${r.status}`);
                }
                return r.json();
            })
            .then(res => {
                if (!res.success || !res.data) {
                    showSearchError(res.message || 'Unable to retrieve medicines.');
                    return;
                }

                // Update result count header
                const countEl = document.getElementById('catalogResultCount');
                if (countEl) {
                    countEl.textContent = `SHOWING ${res.data.count} AVAILABLE MEDICINE(S)`;
                }

                // Update catalog cart button count
                const cartBtnCount = document.getElementById('catalogCartBtnCount');
                if (cartBtnCount) {
                    cartBtnCount.textContent = String(res.data.cart_count);
                }

                const viewCartBtn = document.getElementById('catalogViewCartBtn');
                if (viewCartBtn) {
                    if (res.data.is_customer && res.data.cart_count > 0) {
                        viewCartBtn.classList.remove('d-none');
                    } else {
                        viewCartBtn.classList.add('d-none');
                    }
                }

                renderCatalog(res.data.medicines, res.data.is_logged_in, res.data.is_customer);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                console.error('Live search error:', err);
                showSearchError('Unable to load medicines due to a network connection error. Please try again.');
            });
        };

        const renderCatalog = (medicines, isLoggedIn = false, isCustomer = false) => {
            if (!Array.isArray(medicines) || medicines.length === 0) {
                catalogGrid.innerHTML = `
                    <div class="col-12" id="catalogEmptyState">
                        <div class="card border shadow-sm">
                            <div class="empty-state py-5 text-center">
                                <i class="bi bi-search fs-1 text-secondary mb-2"></i>
                                <h4>No Medicines Found</h4>
                                <p class="text-secondary mb-3">No available medicines matched your search criteria.</p>
                                <a href="index.php?page=catalog" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>View All Medicines
                                </a>
                            </div>
                        </div>
                    </div>`;
                return;
            }

            const safeBadges = ['success', 'warning', 'danger', 'secondary', 'info', 'primary', 'dark'];
            const rawCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const csrfToken = escapeHtml(rawCsrf);

            let html = '';
            medicines.forEach(m => {
                const id = Number(m.id) || 0;
                const categoryName = escapeHtml(m.category_name);
                const name = escapeHtml(m.name);
                const genericName = escapeHtml(m.generic_name);
                const brandName = escapeHtml(m.brand_name || '');
                const dosageForm = escapeHtml(m.dosage_form || m.medicine_type || '');
                const batchNumber = escapeHtml(m.batch_number);
                const manufacturer = escapeHtml(m.manufacturer || '');
                const formattedExpiry = escapeHtml(m.formatted_expiry);
                const formattedPrice = escapeHtml(m.formatted_price);
                const statusLabel = escapeHtml(m.status_label);
                const statusBadge = safeBadges.includes(m.status_badge) ? m.status_badge : 'secondary';
                const quantity = Math.max(0, Number(m.quantity) || 0);
                const inCart = Math.max(0, Number(m.in_cart_quantity) || 0);
                const isMaxStock = inCart >= quantity;

                const rxBadge = m.prescription_required
                    ? '<span class="badge text-bg-danger-subtle text-danger border border-danger-subtle mb-1 d-block text-center" style="font-size: 10px;"><i class="bi bi-prescription2 me-1"></i>Prescription Required</span>'
                    : '<span class="badge text-bg-success-subtle text-success border border-success-subtle mb-1 d-block text-center" style="font-size: 10px;"><i class="bi bi-check-circle me-1"></i>OTC / No Rx</span>';

                let actionControls = '';
                if (!isLoggedIn) {
                    actionControls = `
                        <a href="index.php?page=login" class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Sign In to Order
                        </a>`;
                } else if (isCustomer) {
                    actionControls = `
                        <form action="actions.php" method="post" class="d-flex align-items-center gap-2 ajax-cart-form" data-medicine-id="${id}">
                            <input type="hidden" name="action" value="cart_add">
                            <input type="hidden" name="_csrf" value="${csrfToken}">
                            <input type="hidden" name="medicine_id" value="${id}">
                            <input type="hidden" name="redirect_to" value="catalog">

                            <div class="input-group input-group-sm" style="max-width: 105px;">
                                <span class="input-group-text bg-light text-secondary">Qty</span>
                                <input type="number" name="quantity" class="form-control text-center cart-qty-input" min="1" max="${quantity}" value="1" ${isMaxStock ? 'disabled' : ''}>
                            </div>

                            <button type="submit" class="btn btn-sm btn-success flex-grow-1 cart-submit-btn" ${isMaxStock ? 'disabled' : ''}>
                                <i class="bi bi-cart-plus me-1"></i><span class="btn-label">${inCart > 0 ? 'Add More' : 'Add to Cart'}</span>
                            </button>
                        </form>
                        <div class="mt-2 text-center in-cart-status-wrap ${inCart > 0 ? '' : 'd-none'}">
                            <span class="badge text-bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 11px;">
                                <i class="bi bi-cart-check me-1"></i><span class="in-cart-number">${inCart}</span> in <a href="index.php?page=cart" class="text-primary text-decoration-underline">cart</a> <span class="max-stock-indicator">${isMaxStock ? '(Max available)' : ''}</span>
                            </span>
                        </div>`;
                } else {
                    actionControls = `
                        <div class="text-center py-1">
                            <span class="badge text-bg-secondary-subtle text-secondary small">
                                <i class="bi bi-shield-lock me-1"></i>Staff Mode &middot; <a href="index.php?page=sale_form" class="text-secondary text-decoration-underline">POS New Sale</a>
                            </span>
                        </div>`;
                }

                html += `
                    <div class="col-md-6 col-xl-4 medicine-card-col" data-medicine-id="${id}">
                        <div class="card h-100 shadow-sm border">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge text-bg-light border text-uppercase" style="font-size: 11px;">
                                        <i class="bi bi-tag me-1"></i>${categoryName}
                                    </span>
                                    <span class="badge text-bg-${statusBadge}">
                                        ${statusLabel}
                                    </span>
                                </div>

                                <h5 class="card-title text-success mb-1">${name}</h5>
                                <div class="text-secondary small mb-2">
                                    <strong>Generic:</strong> ${genericName}
                                    ${brandName ? ` &middot; <span class="text-dark">${brandName}</span>` : ''}
                                </div>

                                <div class="small text-muted mb-3">
                                    <div><i class="bi bi-box-seam me-1"></i><strong>Dosage:</strong> ${dosageForm}</div>
                                    <div><i class="bi bi-upc-scan me-1"></i><strong>Batch:</strong> <code>${batchNumber}</code></div>
                                    <div><i class="bi bi-building me-1"></i><strong>Manufacturer:</strong> ${manufacturer}</div>
                                    <div><i class="bi bi-calendar-check me-1"></i><strong>Expiry:</strong> ${formattedExpiry}</div>
                                </div>

                                <div class="mt-auto pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <span class="text-secondary small d-block">Unit Price</span>
                                            <div class="h5 mb-0 text-dark fw-bold">${formattedPrice}</div>
                                        </div>
                                        <div class="text-end">
                                            ${rxBadge}
                                            <span class="badge text-bg-light border text-secondary" style="font-size: 11px;">
                                                In Stock: ${quantity} units
                                            </span>
                                        </div>
                                    </div>

                                    ${actionControls}
                                </div>
                            </div>
                        </div>
                    </div>`;
            });

            catalogGrid.innerHTML = html;
        };

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 300);
        });

        if (categorySelect) {
            categorySelect.addEventListener('change', performSearch);
        }
    }

    // ==========================================
    // 4. Asynchronous AJAX Cart Submission
    // ==========================================
    document.addEventListener('submit', event => {
        const form = event.target.closest('.ajax-cart-form');
        if (!form) return;

        event.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const qtyInput = form.querySelector('input[name="quantity"]');
        const rawMedicineId = form.querySelector('input[name="medicine_id"]')?.value || form.dataset.medicineId;
        const medicineId = parseInt(rawMedicineId, 10);
        const quantity = parseInt(qtyInput?.value || '1', 10);

        const csrfToken = form.querySelector('input[name="_csrf"]')?.value ||
                          document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (!medicineId || isNaN(medicineId) || quantity <= 0 || isNaN(quantity)) {
            showToastNotification('danger', 'Invalid item or quantity.');
            return;
        }

        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Adding...';
        }

        fetch('api/router.php?action=cart_add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                action: 'cart_add',
                medicine_id: medicineId,
                quantity: quantity,
                _csrf: csrfToken
            })
        })
        .then(r => r.json().then(data => ({ status: r.status, ok: r.ok, data })))
        .then(({ status, ok, data }) => {
            if (!ok || !data.success) {
                showToastNotification('danger', data.message || 'Failed to add item to cart.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                return;
            }

            const item = data.data.item;
            const cartCount = data.data.cart_count;

            // Synchronize card-level cart state
            const cardCol = form.closest('.medicine-card-col') || document.querySelector(`.medicine-card-col[data-medicine-id="${medicineId}"]`);
            if (cardCol) {
                const inCartWrap = cardCol.querySelector('.in-cart-status-wrap');
                const inCartNum = cardCol.querySelector('.in-cart-number');
                const maxIndicator = cardCol.querySelector('.max-stock-indicator');
                const labelSpan = form.querySelector('.btn-label');

                if (inCartWrap) inCartWrap.classList.remove('d-none');
                if (inCartNum) inCartNum.textContent = String(item.quantity);
                if (labelSpan) labelSpan.textContent = 'Add More';

                if (item.quantity >= item.max_stock) {
                    if (maxIndicator) maxIndicator.textContent = '(Max available)';
                    if (submitBtn) submitBtn.disabled = true;
                    if (qtyInput) qtyInput.disabled = true;
                } else {
                    if (maxIndicator) maxIndicator.textContent = '';
                    if (submitBtn) submitBtn.disabled = false;
                }
            }

            if (submitBtn && (!item || item.quantity < item.max_stock)) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-cart-plus me-1"></i><span class="btn-label">Add More</span>';
            }

            // Synchronize global cart badges and view-cart button
            const cartBtnCount = document.getElementById('catalogCartBtnCount');
            if (cartBtnCount) cartBtnCount.textContent = String(cartCount);

            const viewCartBtn = document.getElementById('catalogViewCartBtn');
            if (viewCartBtn && cartCount > 0) viewCartBtn.classList.remove('d-none');

            const sidebarCartLinks = document.querySelectorAll('a[href*="page=cart"] span, a[href="index.php?page=cart"] span');
            sidebarCartLinks.forEach(span => {
                if (span.textContent.includes('Shopping Cart') || span.textContent.includes('Cart')) {
                    span.textContent = `Shopping Cart (${cartCount})`;
                }
            });

            showToastNotification('success', data.message || 'Item added to cart.');
        })
        .catch(err => {
            console.error('Cart add error:', err);
            showToastNotification('danger', 'Network error while adding item to cart. Please check your connection.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        });
    });

    // ==========================================
    // 5. Live Order Status Polling Tracker
    // ==========================================
    const trackerMeta = document.getElementById('orderTrackerMetadata');
    if (trackerMeta) {
        const orderId = trackerMeta.getAttribute('data-order-id');
        const initialStatus = trackerMeta.getAttribute('data-status') || '';
        let isTerminal = trackerMeta.getAttribute('data-terminal') === '1';
        const refreshBtn = document.getElementById('liveOrderRefreshBtn');
        const badgeEl = document.getElementById('liveOrderStatusBadge');
        const reloadNotice = document.getElementById('orderReloadNotice');
        let pollInterval = null;

        const safeBadges = ['success', 'warning', 'danger', 'secondary', 'info', 'primary', 'dark'];

        const updateOrderStatusUI = data => {
            if (!data) return;

            const badge = safeBadges.includes(data.order_status_badge) ? data.order_status_badge : 'secondary';
            if (badgeEl) {
                badgeEl.className = `badge text-bg-${badge}`;
                badgeEl.textContent = data.order_status_label;
            }

            // If status changed from the initial loaded status, reveal reload notice
            if (data.order_status !== initialStatus) {
                if (reloadNotice) reloadNotice.classList.remove('d-none');
            }

            if (data.is_terminal) {
                isTerminal = true;
                if (pollInterval) clearInterval(pollInterval);
                if (reloadNotice) reloadNotice.classList.remove('d-none');
            }
        };

        const checkOrderStatus = (isManual = false) => {
            if (!orderId) return;

            let originalBtnHtml = '';
            if (isManual && refreshBtn) {
                refreshBtn.disabled = true;
                originalBtnHtml = refreshBtn.innerHTML;
                refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Checking...';
            }

            fetch(`api/router.php?action=status&id=${encodeURIComponent(orderId)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data) return;
                updateOrderStatusUI(res.data);
            })
            .catch(err => {
                console.error('Order status check error:', err);
            })
            .finally(() => {
                if (isManual && refreshBtn) {
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = originalBtnHtml;
                }
            });
        };

        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                checkOrderStatus(true);
            });
        }

        if (orderId && !isTerminal) {
            pollInterval = setInterval(() => {
                checkOrderStatus(false);
            }, 10000);
        }
    }
});
