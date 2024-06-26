import ImagePicker from 'react-native-image-picker';

export const getImageFromGallary = () => {
  const options = {
    //quality: 0.1,
    maxWidth: 600,
    maxHeight: 500,
    storageOptions: {
      skipBackup: true
    }
  };
  return new Promise((resolve, reject) => {

    ImagePicker.showImagePicker(options, response => {
      if (response.didCancel) {
        console.log("User cancelled photo picker");
      } else if (response.error) {
        console.log("ImagePicker Error: ", response.error);
      } else if (response.customButton) {
        console.log("User tapped custom button: ", response.customButton);
      } else {
        resolve(response)
        //this.onChange(response, key)
      }
    });

  });
}