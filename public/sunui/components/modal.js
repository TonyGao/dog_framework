/**
 * Modal component JavaScript functionality
 */

/**
 * Close the modal window
 * @param {string} modalId - The ID of the modal to close. If not provided, closes all modals.
 */
function closeModal(modalId) {
  if (modalId) {
    // Close specific modal if ID is provided
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = 'none';
      // 触发hide事件
      $(modal).trigger('hide');
    }
  } else {
    // Close all modals if no ID is provided
    const modals = document.querySelectorAll('.ef-modal-container');
    modals.forEach(modal => {
      modal.style.display = 'none';
      // 触发每个模态窗口的hide事件
      $(modal).trigger('hide');
    });
  }
}

/**
 * Open the modal window
 * @param {string} modalId - The ID of the modal to open
 */
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'block';
    // 初始化拖动功能
    initDraggable(modalId);
  }
}

/**
 * 初始化模态窗口的拖动功能
 * @param {string} modalId - 要初始化拖动功能的模态窗口ID
 */
function initDraggable(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  
  const modalContent = modal.querySelector('.ef-modal');
  const modalHeader = modal.querySelector('.ef-modal-header');
  
  if (modalContent && modalHeader) {
    // 检查是否已经初始化过拖动功能
    if (!$(modalContent).hasClass('ui-draggable')) {
      $(modalContent).draggable({
        handle: modalHeader,  // 只能通过头部拖动
        start: function(event, ui) {
          // 确保当前模态窗口在最上层
          const zIndex = parseInt(modal.style.zIndex || 1000);
          modal.style.zIndex = zIndex;
        }
      });
      
      // 添加视觉提示，表明头部可以拖动
      $(modalHeader).css('cursor', 'move');
    }
  }
}

/**
 * 页面加载完成后初始化所有模态窗口的拖动功能
 */
$(document).ready(function() {
  // 为所有已存在的模态窗口添加拖动功能
  $('.ef-modal-container').each(function() {
    const modalId = $(this).attr('id');
    if (modalId && $(this).css('display') === 'block') {
      initDraggable(modalId);
    }
  });
});