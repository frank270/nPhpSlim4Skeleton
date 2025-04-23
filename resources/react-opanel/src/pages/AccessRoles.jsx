import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import { mockRoles, mockPermissions, mockMatrix } from '../data/mockAccessRoles';

function AccessRolesApp() {
  const [groupId, setGroupId] = useState(mockRoles[0].id);
  const [matrix, setMatrix] = useState(() => ({ ...mockMatrix }));
  
  const handlePermissionChange = async ({ groupId, funcId, enabled }) => {
    try {
      const response = await fetch('/opanel/access/update-permission', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ groupId, funcId, enabled })
      });

      if (!response.ok) throw new Error('伺服器錯誤');

      window.Tabler.Toast.show('權限已更新', { color: 'green' });
    } catch (error) {
      window.Tabler.Toast.show(`儲存失敗：${error.message}`, { color: 'red' });
    }
  };
  return (
    <div className="container-xl mt-4">
      <div className="row">
        {/* 左側角色清單 */}
        <div className="col-md-3">
          <div className="card">
            <div className="card-header">
              <h3 className="card-title">角色清單</h3>
            </div>
            <div className="card-body">
              {mockRoles.map((role) => (
                <button
                  key={role.id}
                  onClick={() => setGroupId(role.id)}
                  className={`btn w-100 mb-2 ${
                    role.id === groupId ? 'btn-primary' : 'btn-outline-primary'
                  }`}
                >
                  {role.name}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* 右側預留：權限清單 */}
        <div className="col-md-9">
          <div className="card">
          <div className="card-header">
            <h3 className="card-title">權限清單</h3>
            </div>
            <div className="card-body">
            <table className="table">
                <thead>
                <tr>
                    <th>功能名稱</th>
                    <th className="text-end">是否啟用</th>
                </tr>
                </thead>
                <tbody>
                {mockPermissions.map((perm) => {
                    const enabled = matrix[groupId]?.includes(perm.id);
                    return (
                    <tr key={perm.id}>
                        <td>{perm.name}</td>
                        <td className="text-end">
                        <input
                            type="checkbox"
                            checked={enabled}
                            onChange={(e) => {
                                const checked = e.target.checked;
                              
                                // ✅ 模擬送出 payload
                                console.log('🛰 權限更新準備送出：', {
                                  groupId,
                                  funcId: perm.id,
                                  enabled: checked
                                });
                              
                                // ✅ 本地更新 matrix 狀態
                                setMatrix((prev) => {
                                  const current = prev[groupId] || [];
                                  const updated = checked
                                    ? [...new Set([...current, perm.id])]
                                    : current.filter((id) => id !== perm.id);
                                  return {
                                    ...prev,
                                    [groupId]: updated
                                  };
                                });

                                handlePermissionChange({ groupId, funcId: perm.id, enabled: checked });
                              }}
                            className="form-check-input"
                        />
                        </td>
                    </tr>
                    );
                })}
                </tbody>
            </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('roles-app')).render(
  <React.StrictMode>
    <AccessRolesApp />
  </React.StrictMode>
);
