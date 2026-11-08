/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Unified Client-Side Application Script (Q3 & Q6 Vanilla JS)
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

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

        const performSearch = () => {
            const query = encodeURIComponent(searchInput.value.trim());
            const catId = categorySelect ? encodeURIComponent(categorySelect.value) : '0';
            const url = `api/index.php?action=search&q=${query}&category_id=${catId}`;

            fetch(url)
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data) return;
                    renderCatalog(res.data.medicines);
                })
                .catch(err => console.error('Live search error:', err));
        };

        const renderCatalog = medicines => {
            if (!Array.isArray(medicines) || medicines.length === 0) {
                catalogGrid.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search text-muted" style="font-size: 2.5rem;"></i>
                        <h5 class="mt-3 text-secondary">No medicines found matching your criteria.</h5>
                    </div>`;
                return;
            }

            let html = '';
            medicines.forEach(m => {
                const rxBadge = m.prescription_required
                    ? '<span class="badge bg-danger ms-1"><i class="bi bi-file-earmark-medical me-1"></i>Rx Required</span>'
                    : '<span class="badge bg-success ms-1">OTC</span>';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                html += `
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0 position-relative">
                            <div class="card-body d-flex flex-direction-column justify-content-between p-4">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-light text-dark border">${m.category_name}</span>
                                        ${rxBadge}
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">${m.name}</h5>
                                    <p class="text-muted small mb-2"><em>${m.generic_name}</em> &bull; ${m.dosage_form}</p>
                                    <div class="small text-secondary mb-3">
                                        <span>Batch: <code>${m.batch_number}</code></span><br>
                                        <span>Expires: ${m.formatted_expiry}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h4 fw-bold text-primary mb-0">${m.formatted_price}</span>
                                        <span class="badge bg-${m.status_badge}">${m.status_label} (${m.quantity})</span>
                                    </div>
                                    <form method="post" action="actions.php" class="d-flex gap-2">
                                        <input type="hidden" name="action" value="cart_add">
                                        <input type="hidden" name="_csrf" value="${csrfToken}">
                                        <input type="hidden" name="medicine_id" value="${m.id}">
                                        <input type="number" name="quantity" value="1" min="1" max="${m.quantity}" class="form-control text-center" style="width: 70px;">
                                        <button type="submit" class="btn btn-primary flex-grow-1" ${m.quantity <= 0 ? 'disabled' : ''}>
                                            <i class="bi bi-cart-plus me-1"></i>Add
                                        </button>
                                    </form>
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
    // 4. Live Order Status Polling Tracker
    // ==========================================
    const trackerMeta = document.getElementById('orderTrackerMetadata');
    if (trackerMeta) {
        const orderId = trackerMeta.getAttribute('data-order-id');
        let isTerminal = trackerMeta.getAttribute('data-terminal') === '1';

        if (orderId && !isTerminal) {
            const pollInterval = setInterval(() => {
                fetch(`api/index.php?action=status&id=${orderId}`)
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success || !res.data) return;
                        const data = res.data;

                        const badgeEl = document.getElementById('liveOrderStatusBadge');
                        if (badgeEl) {
                            badgeEl.className = `badge bg-${data.order_status_badge}`;
                            badgeEl.textContent = data.order_status_label;
                        }

                        if (data.is_terminal) {
                            clearInterval(pollInterval);
                            const reloadNotice = document.getElementById('orderReloadNotice');
                            if (reloadNotice) reloadNotice.classList.remove('d-none');
                        }
                    })
                    .catch(err => console.error('Tracker polling error:', err));
            }, 10000);
        }
    }
});
