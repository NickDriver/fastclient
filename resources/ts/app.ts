// FastClient Mini CRM - Main TypeScript Entry

// HTMX is loaded via CDN, declare it globally
declare const htmx: any;

// Theme toggle
document.addEventListener('DOMContentLoaded', () => {
  const themeToggle = document.getElementById('theme-toggle');

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
  }
});

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

// Website scraper - auto-fill customer fields
document.addEventListener('DOMContentLoaded', () => {
  const scrapeBtn = document.getElementById('scrape-btn');
  if (!scrapeBtn) return;

  const websiteInput = document.getElementById('website') as HTMLInputElement;
  const icon = document.getElementById('scrape-icon');
  const spinner = document.getElementById('scrape-spinner');
  const status = document.getElementById('scrape-status');
  const csrfMeta = document.querySelector('input[name="_token"]') as HTMLInputElement;

  const fields = ['name', 'email', 'phone', 'city', 'state', 'industry'];

  function setLoading(loading: boolean) {
    scrapeBtn!.setAttribute('disabled', loading ? 'true' : 'false');
    if (loading) {
      scrapeBtn!.classList.add('opacity-75', 'pointer-events-none');
    } else {
      scrapeBtn!.classList.remove('opacity-75', 'pointer-events-none');
    }
    icon!.classList.toggle('hidden', loading);
    spinner!.classList.toggle('hidden', !loading);
  }

  function showStatus(message: string, type: 'success' | 'info' | 'error') {
    status!.textContent = message;
    status!.className = 'mt-1 text-sm';
    if (type === 'success') {
      status!.classList.add('text-green-600', 'dark:text-green-400');
    } else if (type === 'error') {
      status!.classList.add('text-red-600', 'dark:text-red-400');
    } else {
      status!.classList.add('text-gray-500', 'dark:text-warm-400');
    }
  }

  function highlightField(input: HTMLInputElement) {
    input.classList.add('ring-2', 'ring-green-500');
    setTimeout(() => {
      input.classList.remove('ring-2', 'ring-green-500');
    }, 2000);
  }

  scrapeBtn.addEventListener('click', async () => {
    const url = websiteInput.value.trim();
    if (!url) {
      showStatus('Please enter a website URL first.', 'error');
      websiteInput.focus();
      return;
    }

    setLoading(true);
    showStatus('Fetching website data...', 'info');

    try {
      const response = await fetch('/customers/scrape', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfMeta?.value || '',
        },
        body: JSON.stringify({ url }),
      });

      const result = await response.json();

      if (!result.success) {
        showStatus(result.error || 'Failed to fetch website data.', 'error');
        return;
      }

      const data = result.data || {};
      let filledCount = 0;

      fields.forEach((field) => {
        if (!data[field]) return;

        const input = document.getElementById(field) as HTMLInputElement;
        if (!input) return;

        // Only fill empty fields
        if (input.value.trim() !== '') return;

        input.value = data[field];
        highlightField(input);
        filledCount++;
      });

      if (filledCount > 0) {
        showStatus(`Filled ${filledCount} field${filledCount > 1 ? 's' : ''} from website.`, 'success');
      } else if (Object.keys(data).length > 0) {
        showStatus('Data found but all fields already have values.', 'info');
      } else {
        showStatus('No data could be extracted from this website.', 'info');
      }
    } catch {
      showStatus('Network error. Please try again.', 'error');
    } finally {
      setLoading(false);
    }
  });
});
