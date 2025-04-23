window.showToast = function (message, type = 'info') {
    // 定義不同類型的標題、圖示和顏色
    const titles = {
      success: '成功',
      error: '錯誤',
      info: '訊息',
      warning: '警告'
    };
    
    const icons = {
      success: '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>',
      error: '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>',
      info: '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-info-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>',
      warning: '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>'
    };
    
    // 定義不同類型的顏色樣式
    const colorClasses = {
      success: 'bg-success text-white',
      error: 'bg-danger text-white',
      info: 'bg-info text-white',
      warning: 'bg-warning text-dark'
    };
    
    // 定義標題列的顏色樣式
    const headerColorClasses = {
      success: 'bg-success-subtle',
      error: 'bg-danger-subtle',
      info: 'bg-info-subtle',
      warning: 'bg-warning-subtle'
    };
  
    // 定義圖示的顏色樣式
    const iconColorClasses = {
      success: 'text-success',
      error: 'text-danger',
      info: 'text-info',
      warning: 'text-warning'
    };
  
    // 取得正確的類型，如果不存在則預設為 info
    const validType = ['success', 'error', 'info', 'warning'].includes(type) ? type : 'info';
  
    // 創建 toast 元素
    const toast = document.createElement('div');
    toast.className = `toast show ${colorClasses[validType]}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.setAttribute('data-bs-autohide', 'false');
    toast.setAttribute('data-bs-toggle', 'toast');
  
    // 設定 toast 內容，使用 Tabler 的設計風格
    toast.innerHTML = `
      <div class="toast-header ${headerColorClasses[validType]}">
        <span class="me-2 ${iconColorClasses[validType]}">${icons[validType]}</span>
        <strong class="me-auto">${titles[validType]}</strong>
        <small>剛剛</small>
        <button type="button" class="ms-2 btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">${message}</div>
    `;
  
    // 添加到容器
    const container = document.getElementById('toast-container');
    container?.appendChild(toast);
  
    // 自動隱藏
    setTimeout(() => {
      toast.classList.remove('show');
      toast.addEventListener('transitionend', () => toast.remove());
    }, 7000);
  
    // 強制顯示
    requestAnimationFrame(() => {
      toast.classList.add('show');
    });
    
    // 初始化 Bootstrap Toast
    if (window.bootstrap && window.bootstrap.Toast) {
      const bsToast = new window.bootstrap.Toast(toast);
      bsToast.show();
    }
  };
  