import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../../i18n';

// Components
import CmsList from './CmsList';
import CmsEditor from './CmsEditor';

function CmsApp() {
  const { t } = useTranslation();
  const [view, setView] = useState('list'); // 'list' or 'editor'
  const [editingId, setEditingId] = useState(null);

  // Router logic based on function calls from children
  const handleEdit = (id) => {
    setEditingId(id);
    setView('editor');
  };

  const handleCreate = () => {
    setEditingId(null);
    setView('editor');
  };

  const handleBack = () => {
    setView('list');
    setEditingId(null);
  };

  return (
    <div className="container-fluid">
      {view === 'list' && (
        <CmsList onCreate={handleCreate} onEdit={handleEdit} />
      )}
      {view === 'editor' && (
        <CmsEditor id={editingId} onBack={handleBack} />
      )}
    </div>
  );
}

const root = document.getElementById('cms-app-root');
if (root) {
  ReactDOM.createRoot(root).render(
    <React.StrictMode>
      <CmsApp />
    </React.StrictMode>
  );
}
