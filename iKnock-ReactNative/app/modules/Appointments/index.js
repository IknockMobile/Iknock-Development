import React, {Component} from 'react';
import {FlatList, NetInfo} from 'react-native';
import {Container, Text, View} from 'native-base';
import {ListItems, SearchFieldWithoutIcon} from '../../reuseableComponents';
import styles from '../../assets/styles';
import {getMyAppointment} from '../../actions';
import {connect} from 'react-redux';
import {Toasts} from '../../Utility/showToast';
import {isNull} from '../../Utility';

class MyAppointment extends Component {
  state = {
    isSearching: '',
  };
  componentDidMount = () => {
    this.flatListHandlerFetchData();
  };
  flatListHandlerFetchData = (page = 1, isConcat = true) => {
    const {isSearching} = this.state;

    this.props.getMyAppointment(page, isConcat, isSearching);
  };

  componentDidUpdate() {
    if (this.props.error !== '') {
      Toasts.showToast(this.props.error);
    }
  }
  OnItemPressed = (item, index) => {
    this.props.navigation.navigate('appointmentExcecution', {
      appointmentDetail: item,
      index,
    });
  };
  renderItem = ({item, index}) => {
    const {title, address, appointment_date, media, status, owner} = item;
    return (
      !isNull(title) && (
        <ListItems
          onPress={() => this.OnItemPressed(item, index)}
          name={title}
          address={address}
          appointmantDate={appointment_date}
          thumbnail={media}
          statusCode={status === null ? '' : status.code}
          statusColorCode={status === null ? '#FF0000' : status.color_code}
          isRightButton={0}
          owner={owner === '' ? '' : 'Owner: ' + owner}
          item={item}
        />
      )
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
    return (
      <Container style={styles.container}>
        <View>
          <SearchFieldWithoutIcon
            onChangeText={(text) =>
              this.setState({isSearching: text}, this.flatListHandlerFetchData)
            }
            value={isSearching}
          />
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
            data={this.props.myAppointmentList}
            extraData={this.props.myAppointmentList}
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
    myAppointmentList: state.myAppointment.myAppointmentList,
    error: state.myAppointment.error,
    refreshing: state.myAppointment.refreshing,
    loading: state.myAppointment.loading,
    nextPage: state.myAppointment.nextPage,
    currentPage: state.myAppointment.currentPage,
  };
};
export default connect(mapStateToPrps, {
  getMyAppointment,
})(MyAppointment);
