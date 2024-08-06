(function() {
  class Alert {
    constructor(container) {
      this.container = $(container);
      if (this.container.length === 0) {
        console.error("Container element not found.");
        return;
      }
    }
  
    info(
      content,
      percent,
      title = null,
      closable = false,
      showIcon = true,
      banner = false,
      speed = "normal"
    ) {
      // 定义动画速度
      let animationSpeed;
      switch (speed) {
        case "slow":
          animationSpeed = 2000;
          break;
        case "normal":
          animationSpeed = 1000;
          break;
        case "fast":
          animationSpeed = 500;
          break;
        default:
          animationSpeed = 1000;
      }
  
      let template = `<div role="alert" class="ef-alert ef-alert-info ${
        title ? `ef-alert-with-title` : ""
      }" ${
        banner ? `ef-alert-banner ef-alert-center` : ""
      } style="width: ${percent}; position: absolute; top: 50%;left: 50%;    transform: translate(-50%, -50%);">
      ${
        showIcon
          ? `<div class="ef-alert-icon">
      <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-info-circle-fill" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M24 44c11.046 0 20-8.954 20-20S35.046 4 24 4 4 12.954 4 24s8.954 20 20 20Zm2-30a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-2Zm0 17h1a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-6a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h1v-8a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v11Z" fill="currentColor" stroke="none"></path>
      </svg>
    </div>`
          : ""
      }
  
      <div class="ef-alert-body">
        ${title ? `<div class="ef-alert-title">${title}</div>` : ""}
        <div class="ef-alert-content">${content ? `${content}` : ""}</div>
      </div>
      ${
        closable
          ? `
      <div tabindex="-1" role="button" aria-label="Close" class="ef-alert-close-btn">
        <span class="ef-icon-hover">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-close" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
            <path d="M9.857 9.858 24 24m0 0 14.142 14.142M24 24 38.142 9.858M24 24 9.857 38.142"></path>
          </svg>
        </span>
      </div>`
          : ""
      }
    </div>`;
  
      // Append the template to the container with hide animation
      let alertElement = $(template).hide();
      this.container.append(alertElement);
  
      // Apply fadeIn animation
      alertElement.fadeIn(500);
  
      // Apply fadeOut animation if closable is false
      if (!closable) {
        setTimeout(() => {
          alertElement.fadeOut(animationSpeed, function () {
            // After fadeOut animation, remove the alert from DOM
            $(this).remove();
          });
        }, 3000); // Change the duration as per your requirement
      }
    }
  
    success(
      content,
      percent,
      title = null,
      closable = false,
      showIcon = true,
      banner = false,
      speed = "normal"
    ) {
      // 定义动画速度
      let animationSpeed;
      switch (speed) {
        case "slow":
          animationSpeed = 2000;
          break;
        case "normal":
          animationSpeed = 1000;
          break;
        case "fast":
          animationSpeed = 500;
          break;
        default:
          animationSpeed = 1000;
      }
  
      let template = `<div role="alert" class="ef-alert ef-alert-success ${
        title ? `ef-alert-with-title` : ""
      }" ${
        banner ? `ef-alert-banner ef-alert-center` : ""
      } style="width: ${percent}; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;">
      ${
        showIcon
          ? `<div class="ef-alert-icon">
      <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-check-circle-fill" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M24 44c11.046 0 20-8.954 20-20S35.046 4 24 4 4 12.954 4 24s8.954 20 20 20Zm10.207-24.379a1 1 0 0 0 0-1.414l-1.414-1.414a1 1 0 0 0-1.414 0L22 26.172l-4.878-4.88a1 1 0 0 0-1.415 0l-1.414 1.415a1 1 0 0 0 0 1.414l7 7a1 1 0 0 0 1.414 0l11.5-11.5Z" fill="currentColor" stroke="none"></path>
      </svg>
    </div>`
          : ""
      }
    
      <div class="ef-alert-body">
        ${title ? `<div class="ef-alert-title">${title}</div>` : ""}
        <div class="ef-alert-content">${content ? `${content}` : ""}</div>
      </div>
      ${
        closable
          ? `
      <div tabindex="-1" role="button" aria-label="Close" class="ef-alert-close-btn">
        <span class="ef-icon-hover">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-close" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
            <path d="M9.857 9.858 24 24m0 0 14.142 14.142M24 24 38.142 9.858M24 24 9.857 38.142"></path>
          </svg>
        </span>
      </div>`
          : ""
      }
    </div>`;
  
      // Append the template to the container with hide animation
      let alertElement = $(template).hide();
      this.container.append(alertElement);
  
      // Apply fadeIn animation
      alertElement.fadeIn(1000);
  
      // Apply fadeOut animation if closable is false
      if (!closable) {
        setTimeout(() => {
          alertElement.fadeOut(animationSpeed, function () {
            // After fadeOut animation, remove the alert from DOM
            $(this).remove();
          });
        }, 3000); // Change the duration as per your requirement
      }
    }
  
    warning(
      content,
      percent,
      title = null,
      closable = false,
      showIcon = true,
      banner = false,
      speed = "normal"
    ) {
      // 定义动画速度
      let animationSpeed;
      switch (speed) {
        case "slow":
          animationSpeed = 2000;
          break;
        case "normal":
          animationSpeed = 1000;
          break;
        case "fast":
          animationSpeed = 500;
          break;
        default:
          animationSpeed = 1000;
      }
  
      let template = `<div role="alert" class="ef-alert ef-alert-warning ${
        title ? `ef-alert-with-title` : ""
      }" ${
        banner ? `ef-alert-banner ef-alert-center` : ""
      } style="width: ${percent}; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;">
      ${
        showIcon
          ? `<div class="ef-alert-icon">
      <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-exclamation-circle-fill" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M24 44c11.046 0 20-8.954 20-20S35.046 4 24 4 4 12.954 4 24s8.954 20 20 20Zm-2-11a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v2Zm4-18a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V15Z" fill="currentColor" stroke="none"></path>
      </svg>
    </div>`
          : ""
      }
    
      <div class="ef-alert-body">
        ${title ? `<div class="ef-alert-title">${title}</div>` : ""}
        <div class="ef-alert-content">${content ? `${content}` : ""}</div>
      </div>
      ${
        closable
          ? `
      <div tabindex="-1" role="button" aria-label="Close" class="ef-alert-close-btn">
        <span class="ef-icon-hover">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-close" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
            <path d="M9.857 9.858 24 24m0 0 14.142 14.142M24 24 38.142 9.858M24 24 9.857 38.142"></path>
          </svg>
        </span>
      </div>`
          : ""
      }
    </div>`;
  
      // Append the template to the container with hide animation
      let alertElement = $(template).hide();
      this.container.append(alertElement);
  
      // Apply fadeIn animation
      alertElement.fadeIn(1000);
  
      // Apply fadeOut animation if closable is false
      if (!closable) {
        setTimeout(() => {
          alertElement.fadeOut(animationSpeed, function () {
            // After fadeOut animation, remove the alert from DOM
            $(this).remove();
          });
        }, 3000); // Change the duration as per your requirement
      }
    }
  
    error(
      content,
      percent,
      title = null,
      closable = false,
      showIcon = true,
      banner = false,
      speed = "normal"
    ) {
      // 定义动画速度
      let animationSpeed;
      switch (speed) {
        case "slow":
          animationSpeed = 2000;
          break;
        case "normal":
          animationSpeed = 1000;
          break;
        case "fast":
          animationSpeed = 500;
          break;
        default:
          animationSpeed = 1000;
      }
  
      let template = `<div role="alert" class="ef-alert ef-alert-error ${
        title ? `ef-alert-with-title` : ""
      }" ${
        banner ? `ef-alert-banner ef-alert-center` : ""
      } style="width: ${percent}; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;">
      ${
        showIcon
          ? `<div class="ef-alert-icon">
      <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-close-circle-fill" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M24 44c11.046 0 20-8.954 20-20S35.046 4 24 4 4 12.954 4 24s8.954 20 20 20Zm4.955-27.771-4.95 4.95-4.95-4.95a1 1 0 0 0-1.414 0l-1.414 1.414a1 1 0 0 0 0 1.414l4.95 4.95-4.95 4.95a1 1 0 0 0 0 1.414l1.414 1.414a1 1 0 0 0 1.414 0l4.95-4.95 4.95 4.95a1 1 0 0 0 1.414 0l1.414-1.414a1 1 0 0 0 0-1.414l-4.95-4.95 4.95-4.95a1 1 0 0 0 0-1.414l-1.414-1.414a1 1 0 0 0-1.414 0Z" fill="currentColor" stroke="none"></path>
      </svg>
    </div>`
          : ""
      }
    
      <div class="ef-alert-body">
        ${title ? `<div class="ef-alert-title">${title}</div>` : ""}
        <div class="ef-alert-content">${content ? `${content}` : ""}</div>
      </div>
      ${
        closable
          ? `
      <div tabindex="-1" role="button" aria-label="Close" class="ef-alert-close-btn">
        <span class="ef-icon-hover">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-close" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
            <path d="M9.857 9.858 24 24m0 0 14.142 14.142M24 24 38.142 9.858M24 24 9.857 38.142"></path>
          </svg>
        </span>
      </div>`
          : ""
      }
    </div>`;
  
      // Append the template to the container with hide animation
      let alertElement = $(template).hide();
      this.container.append(alertElement);
  
      // Apply fadeIn animation
      alertElement.fadeIn(1000);
  
      // Apply fadeOut animation if closable is false
      if (!closable) {
        setTimeout(() => {
          alertElement.fadeOut(animationSpeed, function () {
            // After fadeOut animation, remove the alert from DOM
            $(this).remove();
          });
        }, 3000); // Change the duration as per your requirement
      }
    }
  
    normal(
      content,
      percent,
      title = null,
      closable = false,
      showIcon = true,
      banner = false,
      speed = "normal"
    ) {
      // 定义动画速度
      let animationSpeed;
      switch (speed) {
        case "slow":
          animationSpeed = 2000;
          break;
        case "normal":
          animationSpeed = 1000;
          break;
        case "fast":
          animationSpeed = 500;
          break;
        default:
          animationSpeed = 1000;
      }
  
      let template = `<div role="alert" class="ef-alert ef-alert-normal ${
        title ? `ef-alert-with-title` : ""
      }" ${
        banner ? `ef-alert-banner ef-alert-center` : ""
      } style="width: ${percent}; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;">
      ${
        showIcon
          ? `<div class="ef-alert-icon">
      <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-exclamation-circle-fill" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M24 44c11.046 0 20-8.954 20-20S35.046 4 24 4 4 12.954 4 24s8.954 20 20 20Zm-2-11a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v2Zm4-18a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V15Z" fill="currentColor" stroke="none"></path>
      </svg>
    </div>`
          : ""
      }
    
      <div class="ef-alert-body">
        ${title ? `<div class="ef-alert-title">${title}</div>` : ""}
        <div class="ef-alert-content">${content ? `${content}` : ""}</div>
      </div>
      ${
        closable
          ? `
      <div tabindex="-1" role="button" aria-label="Close" class="ef-alert-close-btn">
        <span class="ef-icon-hover">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="ef-icon ef-icon-close" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
            <path d="M9.857 9.858 24 24m0 0 14.142 14.142M24 24 38.142 9.858M24 24 9.857 38.142"></path>
          </svg>
        </span>
      </div>`
          : ""
      }
    </div>`;
  
      // Append the template to the container with hide animation
      let alertElement = $(template).hide();
      this.container.append(alertElement);
  
      // Apply fadeIn animation
      alertElement.fadeIn(1000);
  
      // Apply fadeOut animation if closable is false
      if (!closable) {
        setTimeout(() => {
          alertElement.fadeOut(animationSpeed, function () {
            // After fadeOut animation, remove the alert from DOM
            $(this).remove();
          });
        }, 3000); // Change the duration as per your requirement
      }
    }
  }
})();
