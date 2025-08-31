window.Toast = (() => {
  const container_id = 'toast-container';

  let container = document.getElementById(container_id);
  if (!container) {
    container = document.createElement('div');
    container.id = container_id;
    container.className = 'fixed top-4 right-4 flex flex-col gap-2 z-[9999]';
    document.body.appendChild(container);
  }

  function show({status, message, type = 'default'}, duration = 5000) {    
    const toast = document.createElement('div');

    const base_class = 'text-sm font-semibold px-4 py-3 rounded-lg shadow-lg opacity-0 pointer-events-auto select-none transition-opacity duration-300';
    const type_class = {
      success: 'bg-green-900 text-gray-50',
      error: 'bg-red-900 text-gray-50',
      warning: 'bg-yellow-900 text-red-50',
      info: 'bg-blue-900 text-gray-50',
      default: 'bg-gray-900 text-gray-50'
    };
    const type_icons = {
        success: `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
            </svg>
            `,
        error: `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
            </svg>
            `,
        warning: `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
            </svg>
                `,
        info: `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
            </svg>
            `,
        default: ''
    };

    toast.className = `${base_class} ${type_class[type] || type_class.info}`;

    toast.innerHTML = `
    <div class="flex gap-x-2 items-center">
        ${type_icons[type] || type_icons.info}
        <div class="flex flex-col gap-y-1">
            <div>${status}</div>
            <div class="text-xs">${message}</div>
        </div>
    </div>
    `;
    container.appendChild(toast);

    requestAnimationFrame(() => {
      toast.classList.remove('opacity-0');
      toast.classList.add('opacity-100');
    });

    // Remove toast after duration with fade-out
    setTimeout(() => {
      toast.classList.remove('opacity-100');
      toast.classList.add('opacity-0');

      toast.addEventListener('transitionend', () => {
        toast.remove();
      });
    }, duration);
  }

  return { show };
})();


export default Toast;
