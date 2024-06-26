import React, {Component} from 'react';
import {FlatList, View, TouchableOpacity} from 'react-native';
import {ListItem, Text, Body, Left, Button} from 'native-base';
import {
  SquareButton,
  SquareButtonWithIcon,
  InputFieldWithLabel,
} from '../../reuseableComponents';
import {
  getStatusList,
  changedSummary,
  getTenantUserList,
  addSummaryQuery,
} from '../../actions';
import {connect} from 'react-redux';
import {openExternalApp, mapUrl} from '../../Utility';
import colors from '../../assets/colors';

class SummaryAppointment extends Component {
  constructor(props) {
    super(props);
    this.state = {
      lead_latitude: undefined,
      lead_longitude: undefined,
      currLatLong: undefined,
      lead_detail: {},
      querySummary: props.querySummary,
    };
  }

  componentDidMount() {
    let navigation = this.props.navigation;
    let lead_detail = navigation.getParam('leadDetail');

    this.setState({
      lead_detail,
      lead_latitude: lead_detail.coordinate.latitude,
      lead_longitude: lead_detail.coordinate.longitude,
      currLatLong: navigation.getParam('currLatLong'),
    });
  }

  componentDidUpdate(prevProps) {
    if (prevProps.querySummary !== this.props.querySummary) {
      this.setState({querySummary: this.props.querySummary});
    }
  }

  setDate = newDate => {
    this.setState({chosenDate: newDate});
  };

  onPressStatusHistory = (id, lead_detail) => {
    this.props.navigation.navigate('statusHistory', {
      lead_id: id,
      lead_detail: lead_detail,
    });
  };

  render() {
    const {
      currLatLong,
      address,
      lead_detail,
      lead_latitude,
      lead_longitude,
      querySummary,
    } = this.state;
    const {status, statusTitle, assignPropertyListDialog, userAsignTitle} =
      this.props;
    console.log('PROPS', this.props);
    return (
      // <KeyboardAwareScrollView extraHeight={100} nestedScrollEnabled={true}>
      <View style={{flex: 1, paddingBottom: 0}}>
        <ListItem noBorder>
          <SquareButtonWithIcon
            backgroundColor={status.color_code}
            onPress={this.props.statusListDialog}
            // title={this.props.statusTitle === 'Not Connected' ? this.state.status.title : this.props.statusTitle}
            title={statusTitle}
          />
        </ListItem>
        <ListItem thumbnail noBorder>
          <Left>
            <Button style={{backgroundColor: status.color_code}}>
              <Text>{status.code}</Text>
            </Button>
            {/* <Thumbnail square source={{ uri: 'Image URL' }} /> */}
          </Left>
          <Body>
            {/* "32.7844831, -96.8122981" */}
            <TouchableOpacity
              onPress={() => {
                openExternalApp(
                  mapUrl(currLatLong, lead_latitude + ',' + lead_longitude),
                );
              }}>
              <Text numberOfLines={1}>{lead_detail.title}</Text>
              <Text note numberOfLines={2}>
                {lead_detail.address}, {lead_detail.city} -{' '}
                {lead_detail.zip_code}
              </Text>
              {/* <Text note>{isNull(item.type) ? '' : `Type: ${item.type.title}`}</Text> */}
              {/* <Text note>{lead_detail.owner === "" ? "" : "Owner: " + lead_detail.owner}</Text> */}
            </TouchableOpacity>
          </Body>
        </ListItem>
        <ListItem noBorder>
          <SquareButtonWithIcon
            onPress={assignPropertyListDialog}
            title={userAsignTitle}
            backgroundColor={colors.btnDarkBlueBgColor}
          />
        </ListItem>
        <ListItem noBorder>
          <SquareButton
            onPress={() =>
              this.onPressStatusHistory(lead_detail.id, lead_detail)
            }
            title={'Status History'}
          />
        </ListItem>
        {querySummary.map((item, index) => {
          return (
            <InputFieldWithLabel
              //   onEndEditing={text => console.log(text)}
              //onChangeText={this.onChangeValue}
              onChangeText={text => {
                // this.props.changedSummary(text, item.query_id);
                // console.log(querySummary, text, item);
                this.setState({
                  querySummary: querySummary.map(i =>
                    i.query_id === item.query_id
                      ? {...item, response: text}
                      : i,
                  ),
                });
              }}
              id={item.id}
              index={index}
              query={item.query}
              response={item.response}
            />
          );
        })}
        {/* <FlatList
                        extraData={this.props}
                        data={querySummary}
                        renderItem={({ item, index }) => (
                            <InputFieldWithLabel
                                // onEndEditing={this.onChangeValue}
                                //onChangeText={this.onChangeValue}
                                onChangeText={(text) => this.props.changedSummary(text, item.query_id)}
                                id={item.id}
                                index={index}
                                query={item.query}
                                response={item.response}
                            />
                        )}
                        keyExtractor={(item, index) => item.key + "-" + index}
                    /> */}
        {/* <RoundInputField
                                placeholder={'Enter Name of person you talk to below'} />
                            <RoundInputField
                                placeholder={'Enter Phone # of person you talk to below'} />
                            <RoundInputField
                                placeholder={'Enter Email of person you talk to below'} />
                            <RoundInputField
                                placeholder={'Home Observation Entered Here'} /> */}
        <ListItem noBorder>
          <SquareButton
            onPress={() =>
              this.props.onPressSummaryQuerySubmitBtn(querySummary)
            }
            title={'Save'}
          />
        </ListItem>
      </View>

      //   </KeyboardAwareScrollView>
    );
  }
}

const mapStateToProps = state => {
  return {
    leadDetail: state.summery.leadDetail,
    querySummary: state.summery.querySummary,
    stateList: state.summery.stateList,
    loading: state.summery.loading,
    error: state.summery.error,
    message: state.summery.message,
    tenantUserList: state.summery.tenantUserList,
  };
};
export default connect(mapStateToProps, {
  getStatusList,
  changedSummary,
  getTenantUserList,
  addSummaryQuery,
})(SummaryAppointment);
