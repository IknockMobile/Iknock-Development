import React, {Component} from 'react';
import {
  StyleSheet,
  ImageBackground,
  TouchableOpacity,
  Image,
  TouchableWithoutFeedback,
  Linking,
  Alert,
} from 'react-native';
import {Container, Content, Text, ListItem, View} from 'native-base';
import styles from '../assets/styles';
import colors from '../assets/colors';
import Images from '../assets';
import RNFetchBlob from 'react-native-blob-util';
import {openExternalApp} from '../Utility';
import {Toasts} from '../Utility/showToast';
import _ from 'lodash';
import {FlatListHandler} from '../components';
import {getDeviceWidth} from '../Utility';

let trainer_detail = {};
export default class OwnerIntroduction extends Component {
  static navigationOptions = ({navigation}) => {
    const {params = {}} = navigation.state;
    console.log(params);
    return {
      title: params?.trainer_detail?.title || 'Owner Introduction',
      // headerRight: (
      //     <TouchableOpacity
      //         onPress={() => params.handleThis()}>
      //         <Image style={styles.iconRight} source={Images.ic_pdf_view} />
      //     </TouchableOpacity>

      // ),
    };
  };

  state = {
    imagePath: undefined,
    pdfPath: 'no pdf',
    pdfList: [],
  };
  componentDidMount() {
    // this.props.navigation.setParams({ handleThis: this.onItemPressed });
    const {pdfList} = this.state;

    if (trainer_detail.media.length > 0) {
      trainer_detail.media.map((e, index) => {
        if (e.media_type === 'image') {
          this.setState({
            imagePath: e.path,
          });
        } else {
          pdfList.push(e);
          this.setState({
            pdfList,
          });
        }
      });
    }
  }
  download() {
    var date = new Date();
    var url =
      'http://www.clker.com/cliparts/B/B/1/E/y/r/marker-pin-google-md.png';
    var ext = this.extention(url);
    ext = '.' + ext[0];
    const {config, fs} = RNFetchBlob;
    let PictureDir = fs.dirs.PictureDir;
    let options = {
      fileCache: true,
      addAndroidDownloads: {
        useDownloadManager: true,
        notification: true,
        path:
          PictureDir +
          '/image_' +
          Math.floor(date.getTime() + date.getSeconds() / 2) +
          ext,
        description: 'Image',
      },
    };
    config(options)
      .fetch('GET', url)
      .then(res => {
        Alert.alert('Success Downloaded');
      });
  }
  extention(filename) {
    return /[.]/.exec(filename) ? /[^.]+$/.exec(filename) : undefined;
  }

  onItemPressed = pdfUrl => {
    console.log(pdfUrl);
    openExternalApp(pdfUrl);
    Linking.openURL(pdfUrl);
    // this.props.navigation.navigate('trainerPdfViewer',
    //     {
    //         'pdf': pdfUrl
    //     });
  };
  _renderItem = ({item}) => {
    const {thumb, path} = item;
    return (
      <TouchableWithoutFeedback onPress={() => this.onItemPressed(path)}>
        <View style={Styles.card}>
          <Image
            source={{uri: thumb}}
            defaultSource={{uri: 'https://iknockapp.com/image/pdf.png'}}
            style={{width: getDeviceWidth() / 2 - 32, height: 100}}
            resizeMode={'contain'}
            alt={'PDF'}
          />
          {/* <Text style={{ padding: 8, color: colors.Black }} numberOfLines={2}>{trainer_detail.title}</Text> */}
        </View>
      </TouchableWithoutFeedback>
    );
  };
  render() {
    trainer_detail = this.props.navigation.getParam('trainer_detail');
    const {pdfList, imagePath} = this.state;
    return (
      <Container style={styles.container}>
        <Content>
          {imagePath && (
            <ImageBackground
              style={{width: getDeviceWidth(), height: 200}}
              source={{uri: this.state.imagePath}}
            />
          )}
          <ListItem noBorder>
            <Text note style={{fontSize: 16, color: colors.Black}}>
              {`Description: ${trainer_detail.description}`}
            </Text>
          </ListItem>
          <FlatListHandler
            data={pdfList}
            renderItem={this._renderItem}
            isFetching={false}
            numColumns={2}
          />
        </Content>
      </Container>
    );
  }
}

const Styles = StyleSheet.create({
  card: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 5,
    },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 5,
    backgroundColor: '#fff',
    width: getDeviceWidth() / 2 - 32,
    height: 100,
    margin: 16,
    borderRadius: 16,
    overflow: 'hidden',
  },
});
