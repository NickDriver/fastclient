// FastClient Mini CRM - Main TypeScript Entry

// HTMX is loaded via CDN, declare it globally
declare const htmx: any;

// Mobile sidebar toggle
document.addEventListener('DOMContentLoaded', () => {
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar = document.getElementById('sidebar');
  const sidebarOverlay = document.getElementById('sidebar-overlay');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
      sidebarOverlay?.classList.toggle('hidden');
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
      sidebar?.classList.add('-translate-x-full');
      sidebarOverlay.classList.add('hidden');
    });
  }
});

// HTMX event handlers
document.addEventListener('htmx:afterSwap', (event: Event) => {
  // Re-initialize any components after HTMX swaps content
  console.log('HTMX content swapped');
});

document.addEventListener('htmx:beforeRequest', (event: Event) => {
  // Add any pre-request logic here
});

document.addEventListener('htmx:responseError', (event: Event) => {
  const detail = (event as CustomEvent).detail;
  console.error('HTMX request failed:', detail);
});

// Flash message auto-dismiss
document.addEventListener('DOMContentLoaded', () => {
  const flashMessages = document.querySelectorAll('[data-flash]');
  flashMessages.forEach((message) => {
    setTimeout(() => {
      (message as HTMLElement).style.opacity = '0';
      setTimeout(() => message.remove(), 300);
    }, 5000);
  });
});

// Confirm delete actions
document.addEventListener('click', (event: Event) => {
  const target = event.target as HTMLElement;
  if (target.matches('[data-confirm]')) {
    const message = target.getAttribute('data-confirm') || 'Are you sure?';
    if (!confirm(message)) {
      event.preventDefault();
      event.stopPropagation();
    }
  }
});
