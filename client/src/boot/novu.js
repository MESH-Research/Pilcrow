import NotificationCenterPlugin from '@novu/notification-center-vue';
import '@novu/notification-center-vue/dist/style.css';

export default ({ app }) => {
  app.use(NotificationCenterPlugin)
}

