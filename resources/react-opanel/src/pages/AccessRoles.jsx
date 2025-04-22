import React from 'react';
import ReactDOM from 'react-dom/client';

function AccessRolesApp() {
  return (
    <div className="p-4">
      <h1 className="text-xl font-bold">權限對照頁 (React)</h1>
      <p>這是你整合進 Slim 後台的第一個 React 元件 🎉</p>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('roles-app')).render(
  <React.StrictMode>
    <AccessRolesApp />
  </React.StrictMode>
);
