import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import { mockRoles, mockPermissions, mockMatrix } from '../data/mockAccessRoles';

function AccessRolesApp() {
  const [groupId, setGroupId] = useState(null);
  const [matrix, setMatrix] = useState(() => ({ ...mockMatrix }));
  const [loading, setLoading] = useState(false);
  const [roles, setRoles] = useState([]);
  const [rolesLoading, setRolesLoading] = useState(true);
  const [permissions, setPermissions] = useState([]);

  const handleRoleSelect = (roleId) => {
    setGroupId(roleId);
    setLoading(true);
  
    // Step 1: 抓取權限清單（含勾選狀態）
    fetch(`/opanel/access/group/${roleId}/permissions`)
      .then((res) => res.json())
      .then((data) => {
        setPermissions(data.permissions || []);
      })
      .catch(() => {
        window.Tabler.Toast.show('載入權限失敗', { color: 'red' });
        setPermissions([]); // 顯示為空表
      })
      .finally(() => {
        setLoading(false);
      });
  };
  
  
  useEffect(() => {
    fetch('/opanel/access/roles/list')
      .then((res) => res.json())
      .then((data) => {
        setRoles(data.roles || []);
      })
      .catch(() => {
        window.Tabler.Toast.show('角色清單載入失敗', { color: 'red' });
      })
      .finally(() => {
        setRolesLoading(false);
      });
  }, []);
  
  
  const handlePermissionChange = async ({ groupId, funcId, enabled }) => {
    try {
        // 確保 groupId 和 funcId 是數字
        const formData = new URLSearchParams();
        formData.append('groupId', Number(groupId));
        formData.append('funcId', Number(funcId));
        formData.append('enabled', enabled ? '1' : '0');
        
        console.log('發送資料:', {
            groupId: Number(groupId),
            funcId: Number(funcId),
            enabled: enabled ? '1' : '0'
        }); // 除錯訊息
        
        const response = await fetch('/opanel/access/update-permission', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });

        const result = await response.json(); // 獲取回應內容
        console.log('伺服器回應:', result); // 除錯訊息
        
        if (!result.success) {
            throw new Error(result.message || '伺服器錯誤');
        }

        window.showToast('權限已更新', 'success');
    } catch (error) {
        window.showToast(`更新失敗: ${error.message}`, 'error');
        
        // 還原前端狀態（取消樂觀 UI 更新）
        if (groupId) {
            // 重新載入權限資料
            fetch(`/opanel/access/group/${groupId}/permissions`)
                .then((res) => res.json())
                .then((data) => {
                    setPermissions(data.permissions || []);
                })
                .catch(() => {
                    window.showToast('重新載入權限失敗', 'error');
                });
        }
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
            {rolesLoading ? (
                <div className="text-muted">角色清單載入中...</div>
              ) : roles.length === 0 ? (
                <div className="text-danger">找不到角色資料</div>
              ) : (
                roles.map((role) => (
                  <button
                    key={role.id}
                    onClick={() => handleRoleSelect(role.id)}
                    className={`btn w-100 mb-2 ${
                      role.id === groupId ? 'btn-primary' : 'btn-outline-primary'
                    }`}
                  >
                    {role.name}
                  </button>
                ))
              )}
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
            {groupId === null ? (
              <div className="text-muted">請先選擇一個角色</div>
            ) : loading ? (
              <div className="text-muted">載入中...</div>
            ) : (
              <table className="table">
                <thead>
                  <tr>
                    <th>功能名稱</th>
                    <th className="text-end">是否啟用</th>
                  </tr>
                </thead>
                <tbody>
                {permissions.map((perm) => (
                <tr key={perm.id}>
                    <td>{perm.name}</td>
                    <td className="text-end">
                    <input
                        type="checkbox"
                        className="form-check-input"
                        checked={perm.enabled === 1}
                        disabled={loading}
                        onChange={(e) => {
                        const checked = e.target.checked;

                        // 更新後端
                        handlePermissionChange({
                            groupId,
                            funcId: perm.id,
                            enabled: checked,
                        });

                        // 更新前端狀態（樂觀 UI）
                        setPermissions((prev) =>
                            prev.map((p) =>
                            p.id === perm.id ? { ...p, enabled: checked ? 1 : 0 } : p
                            )
                        );
                        }}
                    />
                    </td>
                </tr>
                ))}
                </tbody>
              </table>
            )}
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
