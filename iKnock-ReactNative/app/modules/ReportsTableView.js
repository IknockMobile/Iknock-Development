import React from 'react';
import {View, Image, TouchableOpacity, FlatList} from 'react-native';
import {
  Grid,
  StackedBarChart,
  XAxis,
  YAxis,
  BarChart,
} from 'react-native-svg-charts';
import {
  Container,
  Content,
  Card,
  CardItem,
  ListItem,
  Left,
  Body,
  Right,
  List,
  Button,
} from 'native-base';
import {GraphIndicator} from '../reuseableComponents';
import Colors from '../assets/colors';
import PopupDialog, {
  SlideAnimation,
  DialogTitle,
} from 'react-native-popup-dialog';
import Images from '../assets';
import styles from '../assets/styles';
import {
  RadioGroup,
  Dropdowns,
  TextView,
  MultiSelectValues,
} from '../reuseableComponents';
import {
  getReportsData,
  getStatusList,
  getTenantUserList,
  getTypeList,
} from '../actions';
import {connect} from 'react-redux';
import {SpinnerView} from '../Utility/common/SpinnerView';
import {Toasts} from '../Utility/showToast';
import {Text} from 'react-native-svg';
import {removeSquareBrackets} from '../Utility';
import {FilterPopup} from '../reuseableComponents/FilterPopup';

class ReportsTableView extends React.PureComponent {
  constructor(props) {
    super(props);
    this.setRef = this.setRef.bind(this);
  }
  state = {
    selectedTimePeriod: '',
    selectedUsers: [],
    selectedTypes: [],
    selectedStatus: [],

    isChecked: true,
    filterCount: 0,
  };
  static navigationOptions = ({navigation}) => {
    const {params = {}} = navigation.state;
    return {
      headerRight: (
        <TouchableOpacity onPress={() => params.handleThis()}>
          <Image style={styles.iconRight} source={Images.ic_filter} />
        </TouchableOpacity>
      ),
    };
  };

  componentDidMount() {
    this.props.navigation.setParams({handleThis: this.onPressFilterButton});
    this.flatListHandlerFetchData();
    // //call api
    this.props.getStatusList(false);
    this.props.getTenantUserList(1, true);
    //this.props.getReportsData(1, true);
    //type list
    this.props.getTypeList(false);
  }
  flatListHandlerFetchData = (page = 1, isConcat = true) => {
    const {selectedTimePeriod, selectedStatus, selectedTypes, selectedUsers} =
      this.state;

    let timePeriod = selectedTimePeriod === -1 ? '' : selectedTimePeriod;

    let selectedStatusId = removeSquareBrackets(selectedStatus);
    let selectedTypesId = removeSquareBrackets(selectedTypes);
    let userIds = removeSquareBrackets(selectedUsers);

    this.props.getReportsData(
      page,
      isConcat,
      userIds,
      timePeriod,
      selectedStatusId,
      selectedTypesId,
      this.cbSuccess,
      this.cbFailer,
    );
  };
  cbSuccess = respponse => {
    //success
  };
  cbFailer = error => {
    Toasts.showToast(error);
  };

  setRef(ref) {
    this.popupDialog = ref;
  }
  onPressFilterButton = () => {
    this.popupDialog.show();
  };
  onChangeUsers = id => {
    this.setState({
      selectedUsers: id,
    });
  };
  onChangeType = id => {
    this.setState({
      selectedTypes: id,
    });
  };
  onChangeTimePeriod = id => {
    this.setState({
      selectedTimePeriod: id,
    });
  };
  onChangeStatus = id => {
    this.setState({
      selectedStatus: id,
    });
  };
  onPrssApplyFilterButton = () => {
    setTimeout(() => this.flatListHandlerFetchData(), 1000);
    this.popupDialog.dismiss();
  };
  onPrssClearAllFilter = () => {
    this.setState(
      {
        selectedTimePeriod: '',
        selectedUsers: [],
        selectedTypes: [],
        selectedStatus: [],
        filterCount: 0,
      },
      this.flatListHandlerFetchData,
    );
    this.popupDialog.dismiss();
  };
  onPressCancelButton = () => {
    this.popupDialog.dismiss();
  };

  handleLoadMore = () => {
    // if (!this.onEndReachedCalledDuringMomentum) {
    //     if (this.props.currentPage !== this.props.nextPage) {
    //         this.props.getLeads(this.props.currentPage + 1);
    //     }
    //     this.onEndReachedCalledDuringMomentum = true;
    // }
  };
  ListEmptyView = () => {
    return (
      <View style={styles.message}>
        <Text style={{textAlign: 'center'}}>
          {this.props.is_loading === true ? 'Loading...' : 'No Record Found.'}
        </Text>
      </View>
    );
  };
  renderSeperator = () => {
    return <View style={styles.dividerItem} />;
  };
  render_FlatList_header = () => {
    var header_View = (
      <View style={styles.itemStyle}>
        <TextView styles={{flex: 1}} title={'Users'} />
        <TextView styles={{flex: 1}} title={'Knocks'} />
        <TextView styles={{flex: 1}} title={'Appointments'} />
      </View>
    );
    return header_View;
  };
  render_FlatList_footer = () => {
    let total_app = 0;
    let total_Leads = 0;
    // let appointment_kept_count_total = 0;

    this.props.reportData.map(e => {
      total_app = total_app + e.appointment_count;
      total_Leads = total_Leads + e.lead_count;
      // appointment_kept_count_total = appointment_kept_count_total + e.appointment_kept_count
    });

    var footer_View = (
      <View style={[styles.itemStyle, {backgroundColor: '#f0b26b'}]}>
        <TextView
          styles={{flex: 2, marginLeft: 10}}
          title={'Total'}
          fontWeight={'bold'}
          color={Colors.Black}
        />
        <TextView
          styles={styles.textAlignment}
          title={total_Leads}
          fontWeight={'bold'}
          color={Colors.Black}
        />
        {/* <TextView
          styles={styles.textAlignment}
          title={total_app}
          fontWeight={'bold'}
          color={Colors.Black}
        /> */}
        {/* <TextView
          styles={styles.textAlignment}
          title={appointment_kept_count_total}
          fontWeight={'bold'}
          color={Colors.Black}
        /> */}
      </View>
    );
    return footer_View;
  };
  render() {
    const {selectedTimePeriod, selectedStatus, selectedTypes, selectedUsers} =
      this.state;

    let appliedFiltersCount = 0;

    if (selectedStatus.length) {
      appliedFiltersCount++;
    }
    if (selectedTypes.length) {
      appliedFiltersCount++;
    }
    if (selectedUsers.length) {
      appliedFiltersCount++;
    }
    if (selectedTimePeriod.length) {
      appliedFiltersCount++;
    }
    return (
      <Container>
        {appliedFiltersCount !== 0 && (
          <TextView
            styles={{justifyContent: 'center', alignItems: 'center'}}
            title={'Applied Filter: ' + appliedFiltersCount}
            fontWeight={'bold'}
            color={Colors.Black}
            fontSize={16}
          />
        )}
        <View style={styles.itemStyle}>
          <TextView
            styles={{flex: 2, marginLeft: 10}}
            title={'Users'}
            fontWeight={'bold'}
            color={Colors.Black}
            fontSize={16}
          />
          <TextView
            styles={styles.textAlignment}
            title={'Knocks'}
            fontWeight={'bold'}
            color={Colors.Black}
            fontSize={14}
          />
          {/* <TextView
            styles={styles.textAlignment}
            title={'Appt Requests'}
            fontWeight={'bold'}
            color={Colors.Black}
            fontSize={13}
            textAlign={'center'}
          /> */}
          {/* <TextView
            styles={styles.textAlignment}
            title={'Appt Kept'}
            fontWeight={'bold'}
            color={Colors.Black}
            fontSize={14}
          /> */}
        </View>
        <FlatList
          // onMomentumScrollBegin={() => { this.onEndReachedCalledDuringMomentum = false; }}
          // onEndReached={this.handleLoadMore}
          // onEndReachedThreshold={0.5}
          onRefresh={() => this.flatListHandlerFetchData()}
          refreshing={this.props.refreshing}
          ListFooterComponent={this.render_FlatList_footer}
          //ListEmptyComponent={this.ListEmptyView}
          ItemSeparatorComponent={this.renderSeperator}
          extraData={this.props.reportData}
          data={this.props.reportData}
          renderItem={({item}) => (
            <View style={styles.itemStyle}>
              <TextView
                title={item.agent_name}
                color={Colors.Black}
                fontSize={16}
                styles={{flex: 2, marginLeft: 10}}
              />
              <TextView
                title={item.lead_count}
                color={Colors.Black}
                styles={styles.textAlignment}
              />
              {/* <TextView
                title={item.appointment_count}
                color={Colors.Black}
                styles={styles.textAlignment}
              /> */}
              {/* <TextView
                title={item.appointment_kept_count}
                color={Colors.Black}
                styles={styles.textAlignment}
              /> */}
            </View>
          )}
          keyExtractor={(item, index) => item.lead_count + '-' + index}
        />

        <FilterPopup
          isUserList={true}
          setRef={this.setRef}
          onPressCancelButton={this.onPressCancelButton}
          onPrssApplyFilterButton={this.onPrssApplyFilterButton}
          onPrssClearAllFilter={this.onPrssClearAllFilter}
          onChangeUser={this.onChangeUsers}
          selectedUser={this.state.selectedUsers}
          selectedTypes={this.state.selectedTypes}
          onChangeType={this.onChangeType}
          selectedTimePeriod={this.state.selectedTimePeriod}
          onChangeTimePeriod={this.onChangeTimePeriod}
          selectedStatus={this.state.selectedStatus}
          onChangeStatus={this.onChangeStatus}
          tenantUserList={this.props.tenantUserList}
          leadTypeList={this.props.leadTypeList}
          stateList={this.props.stateList}
        />

        {/* filter dialog */}
        {/* <PopupDialog dialogStyle={{ width: '100%', height: '100%' }}
                    ref={(popupDialog) => { this.popupDialog = popupDialog; }}
                    dialogAnimation={slideAnimation}>
                    <Container>
                        <View style={{ backgroundColor: Colors.DarkBlue, }}>
                            <ListItem icon noBorder>
                                <Left>
                                    <Button transparent onPress={() => this.onPressTenantCancelButton()}>
                                        <Image style={styles.toggleSize} source={Images.back_arrow_white} />
                                    </Button>
                                </Left>
                                <Body>
                                    <TextView
                                        title={"Filter"}
                                        color={Colors.White}
                                        fontSize={20}
                                        textAlign={'center'}
                                    />
                                </Body>
                                <Right>
                                    <Button transparent onPress={() => this.onPrssApplyFilterButton()}>
                                        <TextView
                                            title={"Apply"}
                                            color={Colors.White}
                                        />
                                    </Button>
                                </Right>
                            </ListItem>
                        </View>
                        <Content>
                            <List>
                                <ListItem noBorder>
                                    <TextView
                                        title={"Search By"}
                                        fontWeight={'bold'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <MultiSelectValues
                                        listItems={this.props.tenantUserList}
                                        selectedItems={this.state.selectedUsers}
                                        onSelectedItemsChange={this.onChangeUsers.bind(this)}
                                        InputPlaceholder={'Search User'}
                                        type={false}
                                    />
                                   
                                </ListItem>
                                <ListItem noBorder>
                                    <TextView
                                        title={"Type"}
                                        fontWeight={'bold'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <MultiSelectValues
                                        listItems={this.props.leadTypeList}
                                        selectedItems={this.state.selectedTypes}
                                        onSelectedItemsChange={this.onChangeType.bind(this)}
                                        InputPlaceholder={'Search Lead Type'}
                                        type={true}
                                    />
                                  
                                </ListItem>
                                <ListItem noBorder>
                                    <TextView
                                        title={"Time Period"}
                                        fontWeight={'bold'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                    <Dropdowns
                                        listItems={radio_time_period}
                                        selectedValue={this.state.selectedTimePeriod}
                                        onValueChange={this.onChangeTimePeriod.bind(this)}
                                        label={'Select Time Period'}
                                        width={330}
                                        placeholder={'Select Time Period'}
                                    />
                                  
                                </ListItem>
                                <ListItem noBorder>
                                    <TextView
                                        title={"Status"}
                                        fontWeight={'bold'}
                                    />
                                </ListItem>
                                <ListItem noBorder>
                                   
                                    <MultiSelectValues
                                        listItems={this.props.stateList}
                                        selectedItems={this.state.selectedStatus}
                                        onSelectedItemsChange={this.onChangeStatus.bind(this)}
                                        InputPlaceholder={'Search Status'}
                                        type={true}
                                    />
                                   
                                </ListItem>
                            </List>
                        </Content>
                    </Container>
                </PopupDialog> */}
        {this.props.is_loading && <SpinnerView />}
      </Container>
    );
  }
}
const slideAnimation = new SlideAnimation({
  slideFrom: 'bottom',
});

const mapStateToProps = state => {
  return {
    reportData: state.report.reportData,
    refreshing: state.report.is_loading,
    error: state.report.error,
    message: state.report.message,
    is_loading: state.report.is_loading,
    stateList: state.summery.stateList,
    tenantUserList: state.summery.tenantUserList,
    leadTypeList: state.summery.leadTypeList,
  };
};
export default connect(mapStateToProps, {
  getReportsData,
  getStatusList,
  getTenantUserList,
  getTypeList,
})(ReportsTableView);
