/**
 * ef-mercure.js
 * 企业框架通用 Mercure (SSE) 客户端库
 */
(function($) {
    'use strict';

    const EF_MERCURE = {
        eventSource: null,
        topics: new Set(),
        listeners: {},
        config: {
            hubUrl: $('meta[name="mercure-hub-url"]').attr('content'),
        },

        /**
         * 初始化 Mercure 连接
         * @param {Object} options 
         */
        init: function(options = {}) {
            if (!this.config.hubUrl) {
                console.warn('Mercure Hub URL not found in meta tags.');
                return;
            }

            if (options.topics) {
                options.topics.forEach(t => this.topics.add(t));
            }

            this.connect();
            this._bindAutoSync();
        },

        /**
         * 建立连接
         */
        connect: function() {
            if (this.eventSource) {
                this.eventSource.close();
                this.eventSource = null;
            }

            if (this.topics.size === 0) {
                // 如果没有任何 topic，不要去连接 Hub，否则会报 400 Bad Request
                return;
            }

            const url = new URL(this.config.hubUrl);
            this.topics.forEach(topic => {
                url.searchParams.append('topic', topic);
            });

            this.eventSource = new EventSource(url, { withCredentials: true });

            this.eventSource.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this._handleMessage(data);
            };

            this.eventSource.onerror = (err) => {
                console.error('Mercure connection error:', err);
                // 浏览器会自动重连，这里可以做一些 UI 提示
            };
        },

        /**
         * 订阅新主题
         * @param {string|string[]} newTopics 
         */
        subscribe: function(newTopics) {
            const topics = Array.isArray(newTopics) ? newTopics : [newTopics];
            let changed = false;
            topics.forEach(t => {
                if (!this.topics.has(t)) {
                    this.topics.add(t);
                    changed = true;
                }
            });

            if (changed) {
                this.connect(); // 重新连接以订阅新主题
            }
        },

        /**
         * 监听特定事件
         * @param {string} type 
         * @param {Function} callback 
         */
        on: function(type, callback) {
            if (!this.listeners[type]) {
                this.listeners[type] = [];
            }
            this.listeners[type].push(callback);
        },

        /**
         * 内部消息处理
         */
        _handleMessage: function(data) {
            // 1. 触发通过 .on() 注册的监听器
            if (data.type && this.listeners[data.type]) {
                this.listeners[data.type].forEach(cb => cb(data));
            }

            // 2. 处理自动同步 (Sync)
            if (data.type === 'sync') {
                this._performAutoSync(data);
            }

            // 3. 处理全局通知
            if (data.type === 'notification') {
                this._showNotification(data);
            }
        },

        /**
         * 自动同步 DOM
         */
        _performAutoSync: function(data) {
            const syncKey = `${data.entity.toLowerCase()}:${data.id}`;
            $(`[data-ef-sync="${syncKey}"]`).each(function() {
                const $el = $(this);
                const refreshUrl = $el.data('ef-sync-url');
                
                if (refreshUrl) {
                    // 如果指定了刷新 URL，则局部加载
                    $el.load(refreshUrl);
                } else {
                    // 否则触发一个自定义事件，让组件自己决定怎么刷新
                    $el.trigger('ef:sync', [data]);
                }
            });
        },

        /**
         * 显示系统通知
         */
        _showNotification: function(data) {
            if (window.ui && window.ui.alert) {
                window.ui.alert.show({
                    title: data.title,
                    content: data.content,
                    type: data.level || 'info',
                    icon: data.icon
                });
            }
        },

        /**
         * 绑定自动同步检测
         */
        _bindAutoSync: function() {
            const self = this;
            // 扫描页面上所有的同步声明，自动订阅主题
            $('[data-ef-sync]').each(function() {
                const syncValue = $(this).data('ef-sync');
                const [entity, id] = syncValue.split(':');
                if (entity && id) {
                    self.subscribe(`/entity/${entity.toLowerCase()}/${id}`);
                }
            });
        },

        /**
         * 开启用户在线状态心跳
         */
        startHeartbeat: function(intervalMs = 30000) {
            const self = this;
            const sendHeartbeat = () => {
                if (window.ef && window.ef.ajax) {
                    window.ef.ajax.post('/api/presence/heartbeat', {}, {
                        quiet: true // 不显示加载条
                    });
                } else {
                    $.post('/api/presence/heartbeat');
                }
            };

            // 立即发送一次
            sendHeartbeat();
            // 定时发送
            setInterval(sendHeartbeat, intervalMs);
        }
    };

    // 暴露给全局 EF 命名空间
    window.EF = window.EF || {};
    window.EF.Mercure = EF_MERCURE;

})(jQuery);
