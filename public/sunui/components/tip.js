(function () {
  class SunTip {
    constructor() {
      this.tipEl = null;
      this.arrowEl = null;
      this.contentEl = null;
      this.activeTarget = null;
      this.init();
    }

    init() {
      // Create tooltip element structure
      this.tipEl = document.createElement('div');
      this.tipEl.className = 'sun-tip';

      this.contentEl = document.createElement('div');
      this.contentEl.className = 'sun-tip-content';
      this.tipEl.appendChild(this.contentEl);

      this.arrowEl = document.createElement('div');
      this.arrowEl.className = 'sun-tip-arrow';
      this.tipEl.appendChild(this.arrowEl);

      document.body.appendChild(this.tipEl);

      // Bind events
      document.addEventListener('mouseover', (e) => {
        const target = e.target.closest('[data-tip], [data-tip-target]');
        if (target) {
          this.show(target);
        }
      });

      document.addEventListener('mouseout', (e) => {
        const target = e.target.closest('[data-tip], [data-tip-target]');
        // Only hide if not moving into the tooltip itself (for interactive tooltips)
        if (target && !this.tipEl.contains(e.relatedTarget)) {
          this.hide();
        }
      });

      // Handle leaving tooltip itself
      this.tipEl.addEventListener('mouseleave', () => {
        this.hide();
      });
    }

    show(target) {
      this.activeTarget = target;
      let content = target.getAttribute('data-tip');
      const targetId = target.getAttribute('data-tip-target');
      const isHtml = target.hasAttribute('data-tip-html') || targetId;
      const theme = target.getAttribute('data-tip-theme'); // light, dark (default)
      const size = target.getAttribute('data-tip-size'); // large, small (default)

      if (targetId) {
        const source = document.querySelector(targetId);
        if (source) content = source.innerHTML;
      }

      // Reset classes
      this.tipEl.className = 'sun-tip';
      if (theme) this.tipEl.classList.add(`sun-tip-${theme}`);
      if (size) this.tipEl.classList.add(`sun-tip-${size}`);

      // Set content
      if (isHtml) {
        this.contentEl.innerHTML = content;
      } else {
        this.contentEl.textContent = content;
      }

      this.tipEl.classList.add('visible');
      this.updatePosition(target);
    }

    updatePosition(target) {
      const rect = target.getBoundingClientRect();
      const tipRect = this.tipEl.getBoundingClientRect();
      const scrollX = window.scrollX || window.pageXOffset;
      const scrollY = window.scrollY || window.pageYOffset;

      // Default position: top
      let top = rect.top + scrollY - tipRect.height - 10;
      let left = rect.left + scrollX + rect.width / 2 - tipRect.width / 2;
      let placement = 'top';

      // Check top boundary
      if (top < scrollY) {
        // Flip to bottom
        top = rect.bottom + scrollY + 10;
        placement = 'bottom';
      }

      // Check left boundary
      if (left < 0) {
        left = 10;
      }
      // Check right boundary
      if (left + tipRect.width > document.documentElement.clientWidth) {
        left = document.documentElement.clientWidth - tipRect.width - 10;
      }

      this.tipEl.style.top = `${top}px`;
      this.tipEl.style.left = `${left}px`;

      // Position arrow
      const arrowLeft = rect.left + scrollX + rect.width / 2 - left - 4; // 4 is half arrow width
      const maxArrowLeft = tipRect.width - 12; // 12 is padding + arrow half width
      this.arrowEl.style.left = `${Math.max(4, Math.min(maxArrowLeft, arrowLeft))}px`;

      if (placement === 'top') {
        this.arrowEl.style.bottom = '-4px';
        this.arrowEl.style.top = 'auto';
        this.arrowEl.style.boxShadow = '2px 2px 2px rgba(0,0,0,0.05)'; // subtle shadow for arrow if light theme
      } else {
        this.arrowEl.style.top = '-4px';
        this.arrowEl.style.bottom = 'auto';
        this.arrowEl.style.boxShadow = '-1px -1px 1px rgba(0,0,0,0.05)';
      }
    }

    hide() {
      this.tipEl.classList.remove('visible');
      this.activeTarget = null;
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => new SunTip());
  } else {
    new SunTip();
  }
})();
