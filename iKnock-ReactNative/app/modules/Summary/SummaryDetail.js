import React, {Component} from 'react';
import {View, StyleSheet, ActionSheetIOS} from 'react-native';
import {Container, ListItem, Left, Text, Content} from 'native-base';
import {SquareButton} from '../../reuseableComponents';
import {connect} from 'react-redux';
import {FlatListHandler} from '../../components';
import {isEmpty} from '../../Utility';

class SummaryDetail extends Component {
  onPressStatusHistory = () => {
    this.props.navigation.navigate('statusHistory', {
      lead_id: this.props.leadDetail.id,
    });
  };
  _renderItem = ({item}) => {
    return (
      <View style={styles.container}>
        <Text>{item.key}</Text>
        <Text>{item.value}</Text>
      </View>
    );
  };
  render() {
    const {
      foreclosure_date,
      admin_notes,
      custom,
      eq,
      sq_ft,
      auction,
      loan_date,
      lead_value,
      original_loan,
      yr_blt,
      mortgagee,
      loan_type,
      loan_mod,
      trustee,
      owner_address,
      source,
      created_by,
      updated_by
    } = this.props.leadDetail;
    const newFields = [
      {key: 'Auction', value: auction},
      {key: 'EQ', value: eq},
      {key: 'Loan Date', value: loan_date},
      {key: 'Lead Value', value: lead_value},
      {key: 'Original Loan', value: original_loan},
      {key: 'Yr Blt', value: yr_blt},
      {key: 'Sq Ft', value: sq_ft},
      {key:'Mortgagee', value:mortgagee},
      {key:'Loan Type', value:loan_mod},
      {key:'Loan Mod', value:loan_type},
      {key:'Trustee', value:trustee},
      {key:'Owner address', value:owner_address},
      {key:'Source', value:source},
      {key:'Created By', value:created_by},
      {key:'Updated By', value:updated_by},

    ];

    return (
      <Container>
        {!isEmpty(admin_notes) && (
          <Text style={{margin: 8}}>
            <Text>{'Admin Notes: '}</Text>
            <Text>{admin_notes}</Text>
          </Text>
        )}
        {!isEmpty(foreclosure_date) && (
          <View style={styles.container}>
            <Text>{'Foreclosure Date'}</Text>
            <Text>{foreclosure_date}</Text>
          </View>
        )}

        <FlatListHandler
          data={[...custom, ...newFields]}
          renderItem={this._renderItem}
          isFetching={false}
          scrollEnabled={false}
        />
      </Container>
    );
  }
}
const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 8,
  },
});
const mapStateToProps = (state) => {
  return {
    leadDetail: state.summery.leadDetail,
  };
};
export default connect(mapStateToProps)(SummaryDetail);
