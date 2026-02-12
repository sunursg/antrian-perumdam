import React from 'react';
import { createRoot } from 'react-dom/client';
import Counter from './pages/Counter';

const mount = document.getElementById('counter-app');

if (mount) {
    const user = JSON.parse(mount.dataset.user || '{}');
    const organization = JSON.parse(mount.dataset.organization || '{}');
    const logoUrl = mount.dataset.logoUrl || '';
    const apiBase = mount.dataset.apiBase || '/counter-api';

    createRoot(mount).render(
        <React.StrictMode>
            <Counter
                user={user}
                organization={organization}
                logoUrl={logoUrl}
                apiBase={apiBase}
            />
        </React.StrictMode>
    );
}
