
import { Toast } from 'native-base';

export const Toasts = {
  showToast: (message, type = "danger") => {
    Toast.show({
      text: message,
      duration: 2500,
      position: 'bottom',
      textStyle: { textAlign: 'center' },
      type: type
      //buttonText: 'Okay',
    });
  },
};
