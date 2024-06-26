import React, { Component } from 'react';
import { FlatList } from 'react-native';
import { Container, View, Text } from 'native-base';
import { ListItems, SearchField, ListFooter } from '../../reuseableComponents';
import styles from '../../assets/styles';
import { getMyLeads, getTenantUserList, getTypeList, getStatusList } from '../../actions';
import { connect } from 'react-redux';
import { Toasts } from "../../Utility/showToast";
import { FilterPopup } from '../../reuseableComponents/FilterPopup';
import { removeSquareBrackets, isNull } from "../../Utility";
import LocationPicker from "../../Utility/LocationPicker";

class MyLeadList extends Component {
    constructor(props) {
        super(props);
        this.currLat = '',
            this.currLong = '',
            this.setRef = this.setRef.bind(this);
    }
    state = {
        selectedTimePeriod: '',
        selectedUser: [],
        selectedTypes: [],
        selectedStatus: [],
        isSearching: '',
        isFilter: true,
        filterCount: 0
    };
    componentDidMount() {
        this.flatListHandlerFetchData();
       
        LocationPicker.checkAndRequestLocation(this._onGetLocationSuccess, this._onGetLocationFailure);
       
        //type list
        this.props.getTypeList(false);
        //set status
        this.props.getStatusList(false);
    }
    _onGetLocationSuccess = (location) => {
        const { latitude, longitude } = location.coords;
            this.setState({
                currLat: latitude.toString(),
                currLong: longitude.toString(),
            });
    }

    _onGetLocationFailure = (error) => {
        const { message } = error;
        Toasts.showToast(message);
    }
    flatListHandlerFetchData = (page = 1, isConcat = true) => {
        const { isSearching, selectedTimePeriod, selectedStatus, selectedTypes, selectedUser } = this.state;
        let selectedStatusId = removeSquareBrackets(selectedStatus);
        let selectedTypesId = removeSquareBrackets(selectedTypes);
        let userIds = removeSquareBrackets(selectedUser);

        this.props.getMyLeads(page, isConcat, isSearching, selectedTimePeriod, userIds,
            selectedStatusId, selectedTypesId, this.cbSuccess, this.cbFailer);
    }
    cbSuccess = (respponse) => {
        //success
    }
    cbFailer = (error) => {
        Toasts.showToast(error)
    }

    OnItemPressed = (item, index) => {
        this.props.navigation.navigate('summary',
            {
                'leadDetail': item,
                index,
                'type': 'myLeadList',
                'currLatLong': this.state.currLat + ',' + this.state.currLong,
            });
    }

    renderSeperator = () => {
        return (
            <View
                style={styles.dividerItem}
            />
        )
    }
    handleLoadMore = () => {
        if (!this.onEndReachedCalledDuringMomentum) {
            if (this.props.currentPage !== this.props.nextPage) {
                this.flatListHandlerFetchData(this.props.currentPage + 1, false);
            }
            this.onEndReachedCalledDuringMomentum = true;
        }
    }
    ListEmptyView = () => {
        return (
            <View style={styles.message}>
                <Text style={{ textAlign: 'center' }}>
                    {
                        this.props.refreshing === true ? 'Loading...' : 'No Record Found.'
                    }
                </Text>
            </View>
        );
    }

    setRef(ref) {
        this.popupDialog = ref;
    }

    onPressFilterButton() {
        this.popupDialog.show();
    }
    onPressCancelButton = () => {
        this.popupDialog.dismiss();
    }
    onPrssApplyFilterButton = () => {
        setTimeout(() => this.flatListHandlerFetchData(), 1000);
        this.popupDialog.dismiss();
    }
    onPrssClearAllFilter = () => {
        this.setState({
            selectedTimePeriod: '',
            selectedUser: [],
            selectedTypes: [],
            selectedStatus: [],
            filterCount: 0
        }, this.flatListHandlerFetchData);
        this.popupDialog.dismiss();
    }
    onChangeTimePeriod = (id) => {
        this.setState({
            selectedTimePeriod: id,
        });
    }
    onChangeUser = (id) => {
        this.setState({
            selectedUser: id,
        });
    }
    onChangeType = (id) => {
        this.setState({
            selectedTypes: id,
        });
    }
    onChangeStatus = (id) => {
        this.setState({
            selectedStatus: id,
        });
    }
    renderListFooter = () => {
        return this.props.currentPage !== this.props.nextPage ? (
            <ListFooter />
        ) : null;
    };
    onChangeText = (text) => {
        if (text.length === 0) {
            this.setState({
                isFilter: true
            });
        } else {
            this.setState({
                isFilter: false
            });
        }
        this.setState({ isSearching: text }, this.flatListHandlerFetchData)
    }
    onClearText = () => {
        this.setState({
            isFilter: true,
            isSearching: ''
        }, this.flatListHandlerFetchData);
    }
    increamentFilterCount = (filterCount) => {
        this.setState({
            filterCount
        })
    }
    render() {
        const { isSearching, isFilter, selectedTimePeriod, selectedStatus, selectedTypes,
            selectedUser } = this.state;
        let appliedFiltersCount = 0;

        if (selectedStatus.length) {
            appliedFiltersCount++;
        }
        if (selectedTypes.length) {
            appliedFiltersCount++;
        }
        // if (selectedUser.length) {
        //     appliedFiltersCount++;
        // }
        if (selectedTimePeriod.length) {
            appliedFiltersCount++;
        }
        return (
            <Container style={styles.container}>
                <View>

                    <SearchField
                        onChangeText={(text) =>
                            this.onChangeText(text)
                        }
                        value={isSearching}
                        onPress={() => this.onPressFilterButton()}
                        isFilter={isFilter}
                        filterCount={appliedFiltersCount}
                        onPressCross={() => this.onClearText()}
                    />
                    <FlatList
                        ref="listRef"
                        style={{ height: '90%' }}
                        onRefresh={() => this.setState({ isSearching: "", isFilter: true }, this.flatListHandlerFetchData)}
                        refreshing={this.props.refreshing}

                        onMomentumScrollBegin={() => { this.onEndReachedCalledDuringMomentum = false; }}
                        onEndReached={this.handleLoadMore}
                        onEndReachedThreshold={0.5}
                        ListEmptyComponent={this.ListEmptyView}
                        ListFooterComponent={this.renderListFooter}
                        data={this.props.myLeadList}
                        extraData={this.props.myLeadList}
                        ItemSeparatorComponent={this.renderSeperator}
                        renderItem={({ item, index }) => (
                            <ListItems
                                onPress={() => this.OnItemPressed(item, index)}
                                name={item.title}
                                address={item.address}
                                date={item.created_at}
                                thumbnail={item?.media[0]?.path}
                                typeCode={isNull(item.type) ? '' : item.type.code}
                                statusCode={item.status.code}
                                statusColorCode={item.status.color_code}
                                isRightButton={1}
                                item={item}
                                leadTypeTitle={isNull(item.type) ? '' : item.type.title}
                            // owner={item.owner === "" ? "" : "Owner: " + item.owner}
                            />
                        )}
                        keyExtractor={(item, index) => item.id + "-" + index}
                    />
                </View>
                <FilterPopup
                    isUserList={false}
                    setRef={this.setRef}
                    onPressCancelButton={this.onPressCancelButton}
                    onPrssApplyFilterButton={this.onPrssApplyFilterButton}
                    onPrssClearAllFilter={this.onPrssClearAllFilter}

                    onChangeUser={this.onChangeUser}
                    selectedUser={this.state.selectedUser}

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
            </Container>
        );
    }
}
const mapStateToPrps = (state) => {
    return {
        myLeadList: state.leads.myLeadList,
        error: state.leads.error,
        refreshing: state.leads.refreshing,
        loading: state.leads.loading,
        nextPage: state.leads.nextPage,
        currentPage: state.leads.currentPage,
        tenantUserList: state.summery.tenantUserList,
        leadTypeList: state.summery.leadTypeList,
        stateList: state.summery.stateList,
    };
};
export default connect(mapStateToPrps, {
    getMyLeads, getTenantUserList, getTypeList, getStatusList
})(MyLeadList);