import React, {Component} from 'react';
import {
  ImageBackground,
  Image,
  FlatList,
  TouchableOpacity,
  Platform,
  Alert,
  KeyboardAvoidingView,
  Linking,
} from 'react-native';
import {
  Container,
  Tabs,
  Tab,
  View,
  Text,
  ListItem,
  Left,
  Right,
  Content,
  Button,
  Body,
} from 'native-base';
import styles from '../../assets/styles';
import colors from '../../assets/colors';
import Images from '../../assets';
import {
  SquareGeryButtonWithIcon,
  SearchFieldWithoutIcon,
  SquareButton,
  SliderImagesWithBottomDots,
} from '../../reuseableComponents';
import SummaryDetail from './SummaryDetail';
import SummaryAppointSchedule from './SummaryAppointSchedule';
import SummaryAppointment from './SummaryAppointment';
import {connect} from 'react-redux';
import {
  getLeadDetail,
  getStatusList,
  getTenantUserList,
  UserAssignLead,
  changeLeadStatus,
  addSummaryQuery,
  addAppointmentQuery,
  updateLeadImage,
  onLeadListUpdate,
} from '../../actions';
import haversine from 'haversine';
import {SpinnerView} from '../../Utility/common/SpinnerView';
import PopupDialog, {
  SlideAnimation,
  DialogTitle,
} from 'react-native-popup-dialog';
import {Toasts} from '../../Utility/showToast';
import {openExternalApp, mapUrl} from '../../Utility';
import ImagePicker from 'react-native-image-crop-picker';
import {isPointWithinRadius} from 'geolib';
import {KeyboardAwareScrollView} from 'react-native-keyboard-aware-scroll-view';
import {splitStr} from '../../Utility';
import {getUserData} from '../../UserPreference';
import LocationPicker from '../../Utility/LocationPicker';

let currLatLong = undefined;
class Summary extends Component {
  static navigationOptions = ({navigation}) => {
    const {params = {}} = navigation.state;
    currLatLong = navigation.getParam('currLatLong');
    const dLatLong =
      navigation.getParam('leadDetail').coordinate.latitude +
      ',' +
      navigation.getParam('leadDetail').coordinate.longitude;
    return {
      title: `${navigation.getParam('leadDetail').address}, ${
        navigation.getParam('leadDetail').city
      } - ${navigation.getParam('leadDetail').zip_code}`,
      headerLeft: (
        <TouchableOpacity
          style={{height: 80, justifyContent: 'center', paddingLeft: 25}}
          onPress={() => params.handleThis()}>
          <Image
            style={{width: 26, height: 26}}
            source={Images.back_arrow_white}
          />
        </TouchableOpacity>
      ),
      headerRight: (
        <TouchableOpacity
          onPress={() => {
            console.log(currLatLong);
            Linking.openURL(
              mapUrl(
                `${currLatLong.latitude},${currLatLong.longitude}`,
                dLatLong,
              ),
            ); //open map for draw route
          }}>
          <Image style={styles.iconRight} source={Images.ic_marker} />
        </TouchableOpacity>
      ),
    };
  };
  constructor(props) {
    super(props);
    this.state = {
      lead_id: '',
      lead: {},
      media: [],
      status: {},
      type: {},
      selectedTenantUserId: '-1',
      selectedTenantUserTitle: 'Assign Property',
      tenantUserTitle: 'Assign Property',
      selectedStatusId: '-1',
      selectedStatusTitle: 'Not Connected',
      statusTitle: 'Not Connected',
      imageResponse: '',
      activeSlide: 0,
      index: 0,
      lead_type: '',
      isSearching: '',
      is_verified: 0,
      userSelectedStatus: '-1',
      distance: 0,
      leadLatLong: {},
      appLatLong: {},
    };
  }
  async componentDidMount() {
    this.props.navigation.setParams({handleThis: this.onChangesText});
    let navigation = this.props.navigation;
    let lead_detail = navigation.getParam('leadDetail');
    let index = navigation.getParam('index');
    let lead_type = navigation.getParam('type');

    const latLongCrr = this.props.navigation.getParam('currLatLong');

    const start = lead_detail.coordinate; //lead coordinate
    
    this.setState({
      leadLatLong: start,
    });
    LocationPicker.checkAndRequestLocation(
      (location) => {
        const {latitude, longitude} = location.coords;
        currLatLong = location.coords;
        this.setState({
          appLatLong: currLatLong,
        });
        
        let val = 0;

        if (
          this._getDistanceFromLatLonInKm(
            start.latitude,
            start.longitude,
            latitude,
            longitude,
          ) < 100
        ) {
          val = 1;
        } else {
          val = 0;
        }
        this.setState({
          is_verified: val,
        });
      },
      (error) => {
        const {message} = error;
        Toasts.showToast(message);
      },
    );

    this.setState({
      index,
      lead_type,
      lead_id: lead_detail.id,
      lead: lead_detail,
      media: this.props.leadDetail.media,
      status: lead_detail.status,

      statusTitle: lead_detail.status.title,
      selectedStatusId: lead_detail.status.id,
      type: lead_detail.type,

      // tenantUserTitle: lead_detail.assignee.name,
      tenantUserTitle:
        JSON.stringify(lead_detail.assignee) === '{}'
          ? 'Assign Property'
          : lead_detail.assignee.name,
      selectedTenantUserId: lead_detail.assignee.id,
      leadVerified: lead_detail.is_verfied || lead_detail.is_verified == '1',
    });
    //get lead detail
    this.props.getLeadDetail(
      lead_detail.id,
      true,
      this.cbSuccess,
      this.cbFailure,
    );

    getUserData().then((response) => {
      console.log('Login User data', JSON.parse(response));
      this.setState({loginUser: JSON.parse(response)});
    });
  }

  _getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    var R = 6371; // Radius of the earth in kilometers
    var dLat = this.deg2rad(lat2 - lat1); // deg2rad below
    var dLon = this.deg2rad(lon2 - lon1);
    var a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(this.deg2rad(lat1)) *
        Math.cos(this.deg2rad(lat2)) *
        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c; // Distance in KM
    
    this.setState({
      distance: d * 1000,
    });
    return d * 1000;
  }

  deg2rad(deg) {
    return deg * (Math.PI / 180);
  }

  cbSuccess = (response) => {
    const assignee = response.data.assignee;
    this.setState({
      // tenantUserTitle: lead_detail.assignee.name,
      tenantUserTitle:
        JSON.stringify(assignee) === '{}' ? 'Assign Property' : assignee.name,
      selectedTenantUserId: assignee.id,
    });
  };

  onChangesText = () => {
    if (this.props.isEditable) {
      this.showAlert();
    } else {
      this.props.navigation.pop();
    }
  };
  showAlert = () => {
    Alert.alert(
      'You Have Unsaved Information',
      'Are You Sure You Want to Move Ahead? ',
      [{text: 'NO'}, {text: 'YES', onPress: () => this.props.navigation.pop()}],
      {cancelable: false},
    );
  };
  componentDidUpdate(prevProps) {
    const {index, lead_type} = this.state;
    if (this.props.message !== '') {
      if (this.props.message !== prevProps.message) {
        Toasts.showToast(this.props.message, 'success');
        //update list
        this.props.onLeadListUpdate(index, this.props.leadDetail, lead_type);
      }
      //on change status
      if (
        this.props.type === 'status' &&
        this.props.leadDetail !== prevProps.leadDetail
      ) {
        //update list
        this.props.onLeadListUpdate(index, this.props.leadDetail, lead_type);
        this.setState({
          status: this.props.leadDetail.status,
          lead: this.props.leadDetail,
          leadVerified:
            this.props.leadDetail.is_verfied ||
            this.props.leadDetail.is_verified == '1',
          statusTitle: this.props.leadDetail.status.title,
        });
      } else if (
        this.props.type === 'assign' &&
        this.props.leadDetail !== prevProps.leadDetail
      ) {
        this.setState({
          lead: this.props.leadDetail,
          leadVerified:
            this.props.leadDetail.is_verfied ||
            this.props.leadDetail.is_verified == '1',
        });
      }
    }
    if (this.props.error !== '') {
      setTimeout(() => {
        Alert.alert('Alert', this.props.error);
      }, 500);
    }
  }
  //status Dialoge
  statusListDialog = () => {
    this.props.getStatusList(false);
    this.setState({userSelectedStatus: '-1'});
    this.popupDialog.show();
  };
  onStatusItemSelect = (item) => {
    this.setState({
      selectedStatusTitle: item.title,
      selectedStatusId: item.id,
      userSelectedStatus: item.id,
    });
  };
  onPressStatusCancelButton = () => {
    this.popupDialog.dismiss();
  };
  onPressStatusOkButton = () => {
    let navigation = this.props.navigation;
    let lead_detail = navigation.getParam('leadDetail');
    const start = lead_detail.coordinate; //lead coordinate
    const {
      selectedStatusId,
      lead_id,
      lead,
      is_verified,
      loginUser,
      userSelectedStatus,
      distance,
      leadLatLong,
      appLatLong
    } = this.state;
    LocationPicker.checkAndRequestLocation(
      (location) => {
        const {latitude, longitude} = location.coords;
        let val = 0;
        let mDistances = this._getDistanceFromLatLonInKm(
          start.latitude,
          start.longitude,
          latitude,
          longitude,
        ); 
        if (
          this._getDistanceFromLatLonInKm(
            start.latitude,
            start.longitude,
            latitude,
            longitude,
          ) < 100
        ) {
          val = 1;
        } else {
          val = 0;
        }
        
        if (userSelectedStatus !== '-1') {
          let params = {
            status_id: userSelectedStatus,
            query: JSON.stringify(
              this.props.querySummary.filter((item) => item.response != ''),
            ),
            is_verified: val,
            is_status_update: 1,
            user_id: lead.assignee.id,
            user_login_id: loginUser[0].id,
            distance: mDistances,
            lead_lat: leadLatLong.latitude,
            lead_long: leadLatLong.longitude,
            application_lat: latitude,
            application_long: longitude,
          };
          console.log('addSummaryQuery Params :: ', params)
          this.props.addSummaryQuery(lead_id, params, true);
          this.setState({userSelectedStatus: '-1'});
          // this.props.changeLeadStatus(this.state.lead_id, this.state.selectedStatusId, true);
          this.popupDialog.dismiss();
        } else {
          Toasts.showToast('Please selcct User');
        }
      },
      (error) => {
        const {message} = error;
        Toasts.showToast(message);
      },
    );
    // const {
    //   selectedStatusId,
    //   lead_id,
    //   lead,
    //   is_verified,
    //   loginUser,
    //   userSelectedStatus,
    //   distance,
    //   leadLatLong,
    //   appLatLong
    // } = this.state;
    // if (userSelectedStatus !== '-1') {
    //   let params = {
    //     status_id: userSelectedStatus,
    //     query: JSON.stringify(
    //       this.props.querySummary.filter((item) => item.response != ''),
    //     ),
    //     is_verified,
    //     is_status_update: 1,
    //     user_id: lead.assignee.id,
    //     user_login_id: loginUser[0].id,
    //     distance: distance,
    //     lead_lat: leadLatLong.latitude,
    //     lead_long: leadLatLong.longitude,
    //     application_lat: appLatLong.latitude,
    //     application_long: appLatLong.longitude,
    //   };
    //   console.log('addSummaryQuery Params :: ', params)
    //   this.props.addSummaryQuery(lead_id, params, true);
    //   this.setState({userSelectedStatus: '-1'});
    //   // this.props.changeLeadStatus(this.state.lead_id, this.state.selectedStatusId, true);
    //   this.popupDialog.dismiss();
    // } else {
    //   Toasts.showToast('Please selcct User');
    // }
  };
  cbStateSuccess = (response) => {};
  cbFailure = (error) => {};
  //tenant User Dialog
  assignPropertyListDialog = () => {
    this.popupDialogTenantUser.show();

    this.flatListHandlerFetchData();
  };
  flatListHandlerFetchData = (page = 1, isConcat = true) => {
    const {isSearching} = this.state;
    this.props.getTenantUserList(page, isConcat, isSearching);
  };

  onTenantUserSelect = (item) => {
    this.setState({
      selectedTenantUserTitle: item.name,
      selectedTenantUserId: item.id,
    });
  };
  onPressTenantCancelButton = () => {
    this.popupDialogTenantUser.dismiss();
  };
  onPressTenantOkButton = () => {
    const {loginUser} = this.state;

    this.setState({
      tenantUserTitle: this.state.selectedTenantUserTitle,
    });
    if (this.state.selectedTenantUserId !== '-1') {
      this.props.UserAssignLead(
        this.state.lead_id,
        this.state.selectedTenantUserId,
        true,
        loginUser[0].id,
      );
      this.popupDialogTenantUser.dismiss();
    } else {
      Toasts.showToast('Please selcct User');
    }
  };
  onPressSummaryQuerySubmitBtn = (querySummaryData) => {
    const {selectedStatusId, lead_id, lead, loginUser, is_verified, distance, leadLatLong, appLatLong} =
      this.state;

    let params = {
      status_id: selectedStatusId,
      query: JSON.stringify(
        querySummaryData.filter((item) => item.response != ''),
      ),
      is_status_update: 0,
      user_id: lead.assignee.id,
      user_login_id: loginUser[0].id,
      is_verified,
      distance: distance,
      lead_lat: leadLatLong.latitude,
      lead_long: leadLatLong.longitude,
      application_lat: appLatLong.latitude,
      application_long: appLatLong.longitude,
    };
    console.log('addSummaryQuery Params :: ', params)
    this.props.addSummaryQuery(lead_id, params, true);
  };
  onPressAppointQuerySubmitBtn = (isUpdated) => {
    if (this.props.queryAppointment.length > 0) {
      if (this.props.queryAppointment[0].response === '') {
        Toasts.showToast(
          this.props.queryAppointment[0].query + ' is required field',
        );
      } else {
        if (this.props.dateTime === '') {
          Toasts.showToast('Please select date and time');
        } else {
          const filterPhone = this.props.queryAppointment.filter(
            (item) => item.query == 'Phone',
          );
          const appointment_phone = filterPhone[0]?.response;
          console.log('Apoo', appointment_phone);

          this.props.addAppointmentQuery(
            this.state.lead_id,
            this.props.dateTime,
            this.props.queryAppointment,
            true,
            isUpdated,
            appointment_phone,
          );
        }
      }
    }
  };
  handleLoadMore = () => {
    if (!this.onEndReachedCalledDuringMomentum) {
      if (this.props.currentPage !== this.props.nextPage) {
        this.flatListHandlerFetchData(this.props.currentPage + 1, false);
      }
      this.onEndReachedCalledDuringMomentum = true;
    }
  };
  onPressToChangeImage = () => {
    ImagePicker.openPicker({
      width: 300,
      height: 400,
      // cropping: true,
      multiple: true,
      compressImageQuality: 0.6,
      maxFiles: 20,
    }).then((response) => {
      console.log('response', response);
      this.props.updateLeadImage(this.state.lead_id, response, true);

      this.setState({
        media: response,
      });
    });
  };
  ListEmptyView = () => {
    return (
      <View style={styles.message}>
        <Text style={{textAlign: 'center'}}>No Record Found.</Text>
      </View>
    );
  };
  renderSeperator = () => {
    return <View style={styles.dividerItem} />;
  };
  render() {
    const {media, activeSlide, lead, leadVerified} = this.state;

    console.log('lead', lead, leadVerified);
    return (
      <View style={{flex: 1}}>
        <Content>
          <View>
            {this.props.leadDetail?.media &&
            this.props.leadDetail?.media?.length > 0 ? (
              <SliderImagesWithBottomDots
                entries={this.props.leadDetail.media || []}
                onSelectedItem={(index) => this.setState({activeSlide: index})}
                activeSlide={activeSlide}
              />
            ) : (
              <>
                <View style={{height: 86}}></View>
              </>
            )}
            <View
              style={[
                styles.transparatBg,
                {position: 'absolute', top: 0, width: '100%'},
              ]}>
              <Text
                style={{
                  color: this.state.status.color_code,
                  fontWeight: 'bold',
                  fontSize: 20,
                }}>
                Verified: {leadVerified ? 'Yes' : 'No'}
              </Text>
              <TouchableOpacity onPress={() => this.onPressToChangeImage()}>
                <Image style={styles.iconsRight} source={Images.ic_edit} />
              </TouchableOpacity>
            </View>
            <View
              style={[
                styles.transparatBg,
                {position: 'absolute', bottom: 0, width: '100%'},
              ]}>
              <Text
                style={{
                  color: this.state.status.color_code,
                  fontWeight: 'bold',
                  fontSize: 18,
                  width: '45%',
                }}
                numberOfLines={1}>
                {this.state.status.title}
              </Text>
              <Text
                style={{
                  color: this.state.status.color_code,
                  fontWeight: 'bold',
                  fontSize: 18,
                  width: '45%',
                  textAlign: 'right',
                }}
                numberOfLines={1}>
                {this.state.type === null ? '' : this.state.type.title}
              </Text>
            </View>
            {/* <ImageBackground style={{ width: '100%', height: 230 }}
                            source={{
                                uri: this.state.imageResponse === '' ? this.state.media.path : this.state.imageResponse
                            }}>
                            <View style={styles.transparatBg}>
                                <Text style={{ color: this.state.status.color_code, fontWeight: 'bold', fontSize: 20 }}>
                                    Verified: {this.state.lead.is_verfied === true ? 'Yes' : 'No'}
                                </Text>
                                <TouchableOpacity
                                    onPress={() => this.onPressToChangeImage()}>
                                    <Image style={styles.iconsRight} source={Images.ic_edit} />
                                </TouchableOpacity>
                            </View>
                            <View style={{ flex: 1, justifyContent: 'flex-end' }}>
                                <View style={styles.transparatBg}>
                                    <Text style={{ color: this.state.status.color_code, fontWeight: 'bold', fontSize: 20 }}>
                                        {this.state.status.title}
                                    </Text>
                                    <Text style={{ color: this.state.status.color_code, fontWeight: 'bold', fontSize: 20 }}>
                                        {this.state.type.title}
                                    </Text>
                                </View>
                            </View>
                        </ImageBackground> */}
          </View>
          <KeyboardAwareScrollView
            enableOnAndroid
            enableAutomaticScroll
            keyboardOpeningTime={0}
            extraHeight={Platform.select({android: 200, ios: -50})}
            nestedScrollEnabled>
            <Tabs
              tabBarUnderlineStyle={{
                borderBottomWidth: 4,
                borderBottomColor: colors.DarkBlueTextColor,
              }}>
              <Tab
                heading="Summary"
                tabStyle={{backgroundColor: colors.White}}
                textStyle={{color: colors.DarkGery}}
                activeTabStyle={{backgroundColor: colors.White}}
                activeTextStyle={{
                  color: colors.DarkBlueTextColor,
                  fontWeight: 'normal',
                }}>
                <SummaryAppointment
                  navigation={this.props.navigation}
                  assignPropertyListDialog={this.assignPropertyListDialog}
                  statusListDialog={this.statusListDialog}
                  statusTitle={this.state.statusTitle}
                  status={this.state.status}
                  userAsignTitle={this.state.tenantUserTitle}
                  onPressSummaryQuerySubmitBtn={
                    this.onPressSummaryQuerySubmitBtn
                  }
                />
              </Tab>
              <Tab
                heading="Details"
                tabStyle={{backgroundColor: colors.White}}
                textStyle={{color: colors.DarkGery}}
                activeTabStyle={{backgroundColor: colors.White}}
                activeTextStyle={{
                  color: colors.DarkBlueTextColor,
                  fontWeight: 'normal',
                }}>
                <SummaryDetail navigation={this.props.navigation} />
              </Tab>
              <Tab
                heading="Appoint Schedule"
                tabStyle={{backgroundColor: colors.White}}
                textStyle={{color: colors.DarkGery}}
                activeTabStyle={{backgroundColor: colors.White}}
                activeTextStyle={{
                  color: colors.DarkBlueTextColor,
                  fontWeight: 'normal',
                }}>
                <SummaryAppointSchedule
                  navigation={this.props.navigation}
                  onPressAppointQuerySubmitBtn={
                    this.onPressAppointQuerySubmitBtn
                  }
                />
              </Tab>
            </Tabs>
          </KeyboardAwareScrollView>
        </Content>

        {/* Status List Start */}
        <PopupDialog
          dialogStyle={{width: '100%', height: '80%'}}
          ref={(popupDialog) => {
            this.popupDialog = popupDialog;
          }}
          dialogAnimation={slideAnimation}>
          <ListItem noBorder>
            <Text>Change Lead Status</Text>
          </ListItem>
          <FlatList
            style={{backgroundColor: '#fff'}}
            ListEmptyComponent={this.ListEmptyView}
            data={this.props.stateList}
            extraData={this.props.stateList}
            renderItem={({item}) => (
              <SquareGeryButtonWithIcon
                onPress={() => this.onStatusItemSelect(item)}
                id={item.id}
                title={item.title}
                selectedItemId={this.state.userSelectedStatus}
                textColor={colors.Black}
                backgroundColor={item.color_code}
              />
            )}
            keyExtractor={(item, index) => item.id + '-' + index}
          />
          <ListItem noBorder>
            <View style={{flexDirection: 'row'}}>
              <SquareButton
                onPress={() => this.onPressStatusCancelButton()}
                title={'Cancel'}
              />
              <SquareButton
                onPress={() => this.onPressStatusOkButton()}
                title={'ok'}
              />
            </View>
          </ListItem>
        </PopupDialog>
        {/* Status List End */}
        {/* Tenant User Start */}
        <PopupDialog
          dialogStyle={{width: '100%', height: '100%'}}
          ref={(popupDialog) => {
            this.popupDialogTenantUser = popupDialog;
          }}
          dialogAnimation={slideAnimation}>
          <View style={{backgroundColor: colors.DarkBlue}}>
            <ListItem icon noBorder>
              <Left>
                <Button
                  transparent
                  onPress={() => this.onPressTenantCancelButton()}>
                  <Image
                    style={styles.toggleSize}
                    source={Images.back_arrow_white}
                  />
                </Button>
              </Left>
              <Body>
                <Text style={{color: colors.White}}>Assign Property</Text>
              </Body>
            </ListItem>
          </View>
          <SearchFieldWithoutIcon
            onChangeText={(text) =>
              this.setState({isSearching: text}, this.flatListHandlerFetchData)
            }
            value={this.state.isSearching}
          />
          <FlatList
            style={{backgroundColor: '#fff', height: '100%'}}
            onRefresh={() =>
              this.setState({isSearching: ''}, this.flatListHandlerFetchData)
            }
            refreshing={this.props.refreshing}
            // onMomentumScrollBegin={() => { this.onEndReachedCalledDuringMomentum = false; }}
            // onEndReached={this.handleLoadMore}
            // onEndReachedThreshold={0.5}
            ListEmptyComponent={this.ListEmptyView}
            ItemSeparatorComponent={this.renderSeperator}
            data={this.props.tenantUserList}
            extraData={this.props.tenantUserList}
            renderItem={({item}) => (
              <ListItem noBorder onPress={() => this.onTenantUserSelect(item)}>
                <Left>
                  <Text>{item.name}</Text>
                </Left>
                <Right>
                  {
                    <Image
                      source={
                        this.state.selectedTenantUserId === item.id
                          ? Images.ic_checked_blue
                          : Images.ic_unchecked
                      }
                      style={{width: 25, height: 25}}
                    />
                  }
                </Right>
              </ListItem>
            )}
            keyExtractor={(item, index) => item.id + '-' + index}
          />
          <ListItem noBorder>
            <SquareButton
              onPress={() => this.onPressTenantOkButton()}
              title={'Save'}
            />
          </ListItem>
        </PopupDialog>
        {/* Tenant User End */}
        {this.props.loading && <SpinnerView />}
      </View>
    );
  }
}

const slideAnimation = new SlideAnimation({
  slideFrom: 'bottom',
});

const mapStateToProps = (state) => {
  return {
    stateList: state.summery.stateList,
    leadDetail: state.summery.leadDetail,
    querySummary: state.summery.querySummary,
    queryAppointment: state.summery.queryAppointment,
    loading: state.summery.loading,
    refreshing: state.summery.refreshing,
    error: state.summery.error,
    message: state.summery.message,
    tenantUserList: state.summery.tenantUserList,
    dateTime: state.summery.dateTime,
    nextPage: state.summery.nextPage,
    currentPage: state.summery.currentPage,
    type: state.summery.type,
    isEditable: state.summery.isEditable,
  };
};
export default connect(mapStateToProps, {
  getStatusList,
  getLeadDetail,
  getTenantUserList,
  UserAssignLead,
  changeLeadStatus,
  addSummaryQuery,
  addAppointmentQuery,
  updateLeadImage,
  onLeadListUpdate, //lead action for update list
})(Summary);
