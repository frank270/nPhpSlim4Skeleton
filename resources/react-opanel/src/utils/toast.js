window.showToast = function (message = '成功', type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
  
    const toastId = `toast-${Date.now()}`;
    const colorMap = {
      success: 'text-bg-success',
      error: 'text-bg-danger',
      info: 'text-bg-info',
      warning: 'text-bg-warning',
    };
    const colorClass = colorMap[type] || 'text-bg-secondary';
  
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center ${colorClass} border-0 mb-2`;
    toastEl.id = toastId;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
  
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
  
    container.appendChild(toastEl);
  
    // 手動觸發顯示（Tabler 內含 bootstrap.Toast）
    const toast = new bootstrap.Toast(toastEl, { delay: 7000 });
    toast.show();
  
    // 清除 DOM 節點
    toastEl.addEventListener('hidden.bs.toast', () => {
      toastEl.remove();
    });
  };
  