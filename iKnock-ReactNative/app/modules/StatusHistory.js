import React, {Component} from 'react';
import {FlatList, Text,Image} from 'react-native';
import {Container, View} from 'native-base';
import {
  ListItemStatusHistory,
  SearchFieldWithoutIcon,
} from '../reuseableComponents';
import styles from '../assets/styles';
import {getStatusHistory} from '../actions';
import {connect} from 'react-redux';
import {Toasts} from '../Utility/showToast';
import {isNull} from '../Utility';
import Images from '../assets';
import colors from '../assets/colors';

class StatusHistory extends Component {
  constructor(props) {
    super(props);
    this.state = {
      lead_id: '',
      isSearching: '',
    };
  }
  componentDidMount = () => {
    let navigation = this.props.navigation;
    let lead_id = navigation.getParam('lead_id');

    this.setState(
      {
        lead_id,
      },
      this.flatListHandlerFetchData,
    );
  };
  flatListHandlerFetchData = (page = 1, isConcat = true) => {
    const {isSearching, lead_id} = this.state;
    this.props.getStatusHistory(
      page,
      isConcat,
      lead_id,
      isSearching,
      this.cbSuccess,
      this.cbFailer,
    );
  };
  cbSuccess = (respponse) => {
    //success
  };
  cbFailer = (error) => {
    console.log(error)
    Toasts.showToast(error);
  };
  // componentDidUpdate() {
  //     if (this.props.error !== '') {
  //         Toasts.showToast(this.props.error)
  //     }
  // }

  renderItem = ({item}) => {
    const {title, address, created_at, image, assign, type, status, owner, latest_status} =
      item;
    return (
      <ListItemStatusHistory
        name={title}
        address={address}
        date={created_at}
        thumbnail={image}
        userName={isNull(assign) ? '' : assign.name}
        typeCode={type.code}
        statusTitle={status.title}
        statusCode={status.code}
        statusColorCode={status.color_code}
        isRightButton={1}
        owner={owner === '' ? '' : 'Owner: ' + owner}
        latest_status={latest_status}
      />
    );
  };
  renderSeperator = () => {
    return <View style={styles.dividerItem} />;
  };
  handleLoadMore = () => {
    if (!this.onEndReachedCalledDuringMomentum) {
      if (this.props.currentPage !== this.props.nextPage) {
        this.flatListHandlerFetchData(this.props.currentPage + 1, false);
      }
      this.onEndReachedCalledDuringMomentum = true;
    }
  };
  ListEmptyView = () => {
    return (
      <View style={styles.message}>
        <Text style={{textAlign: 'center'}}>
          {this.props.refreshing === true ? 'Loading...' : 'No Record Found.'}
        </Text>
      </View>
    );
  };
  render() {
    const {isSearching} = this.state;
    const {statusHistoryList} = this.props;
    let lead_detail = this.props.navigation.getParam('lead_detail');
    return (
      <Container style={styles.container}>
        <View>
          <SearchFieldWithoutIcon
            onChangeText={(text) =>
              this.setState({isSearching: text}, this.flatListHandlerFetchData)
            }
            value={isSearching}
          />
          <View style={{flexDirection: 'row', marginHorizontal:25, marginTop:20}}>
            
            <View
              style={{
                flexDirection: 'column',
                marginLeft: 10,
                marginRight: 60,
              }}>
              <Text style={{color: colors.DarkBlue}}>
                {lead_detail?.title}
              </Text>
              <Text note numberOfLines={2}>
                {lead_detail?.address}  {lead_detail?.city}, {lead_detail?.state} {lead_detail?.zip_code}
              </Text>
              <Text note>{statusHistoryList[0]?.owner}</Text>
            </View>
          </View>
          <FlatList
            style={{height: '90%'}}
            onRefresh={() =>
              this.setState({isSearching: ''}, this.flatListHandlerFetchData)
            }
            refreshing={this.props.refreshing}
            onMomentumScrollBegin={() => {
              this.onEndReachedCalledDuringMomentum = false;
            }}
            onEndReached={this.handleLoadMore}
            onEndReachedThreshold={0.5}
            ListEmptyComponent={this.ListEmptyView}
            data={this.props.statusHistoryList}
            extraData={this.props.statusHistoryList}
            ItemSeparatorComponent={this.renderSeperator}
            renderItem={this.renderItem}
            keyExtractor={(item, index) => item.id + '-' + index}
          />
        </View>
      </Container>
    );
  }
}
const mapStateToPrps = (state) => {
  return {
    statusHistoryList: state.statusHistory.statusHistoryList,
    error: state.statusHistory.error,
    refreshing: state.statusHistory.refreshing,
    loading: state.statusHistory.loading,
    nextPage: state.statusHistory.nextPage,
    currentPage: state.statusHistory.currentPage,
  };
};
export default connect(mapStateToPrps, {
  getStatusHistory,
})(StatusHistory);
