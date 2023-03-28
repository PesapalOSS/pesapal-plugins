var Container = React.createClass({
    getInitialState: function () {
        return {
            fetched: false,
            response: {
                status:{},
                result:{}
            },
            currentTab: 1,
            tabs: {
                one: 'complete',
                two: 'complete',
                three: 'active'
            }
        }
    },
    componentWillMount: function () {
        this.checkStatus();
    },
    checkStatus: function () {
        var reactThis = this;
        var update = {
            pesapal_merchant_reference: this.urlParam('pesapal_merchant_reference'),
            pesapal_transaction_tracking_id: this.urlParam('pesapal_transaction_tracking_id')
        }
        ajaxPostReact('index.php?option=com_pesapal&task=checkStatus&format=raw', update, function (result) {
            reactThis.setState({fetched: true});
            reactThis.setState({response: result});
        })
    },
    urlParam: function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        }
        else {
            return results[1] || 0;
        }
    },
    getPaymentText(){

        var {status} = this.state.response.status;
        var {result} = this.state.response;
        var text = "";
        switch (status) {
            case 'COMPLETED':
                text = <h4 class="text-center">
                    <span>Thank you,</span> your transaction has been completed successfully.
                </h4>;
                break;
            case 'PENDING':
                text = <span><h4>Payment Pending</h4>
Thank you {result.firstname},
Your payment  is being processed. Once
confirmed, you will receive an Email/SMS notification, and your payment settled instantly.Thank
you for using PesaPal.</span>;
                break;
            case 'FAILED':
                text = <span><h4>Payment Failed</h4>
<p>We have noted your payment has failed. This could be because of several reasons;</p>
<ol>
    <li>The card details you entered are incorrect.</li>
    <li>Your bank may have blocked online payments.</li>
    <li>You have insufficient funds in the card/mobile money account you are attempting to use.</li>
    <li>Your bank may have declined this transaction, kindly check with your bank.</li>
</ol>

                  <p>Would you like to make the payment again? Please click <a
                      href="index.php?option=com_pesapal">here</a></p></span>;
                break;
            default:
                text = <span><h4>Payment Failed</h4>
<p>We have noted your payment has failed. This could be because of several reasons;</p>
<ol>
    <li>The card details you entered are incorrect.</li>
    <li>Your bank may have blocked online payments.</li>
    <li>You have insufficient funds in the card/mobile money account you are attempting to use.</li>
    <li>Your bank may have declined this transaction, kindly check with your bank.</li>
</ol>

                  <p>Would you like to make the payment again? Please click <a
                      href="index.php?option=com_pesapal">here</a></p></span>;

        }
        return text;
    },
    getPaymentColor(){
        var {status} = this.state.response.status;
        var color = "";
        switch (status) {
            case 'COMPLETED':
                color = 'alert-message-success';
                break;
            case 'PENDING':
                color = 'alert-message-default';
                break;
            case 'FAILED':
                color = 'alert-message-danger';
                break;
            default:
                color = 'alert-message-danger';

        }
        return color;
    },
    renderSteps: function () {
        return <div id="bootstrap-wizard-1" className="col-sm-12">
            <div className="form-bootstrapWizard">
                <ul className="bootstrapWizard form-wizard">
                    <li className={this.state.tabs.one}>
                         <span className="step">1</span> <span
                            className="title">Basic information</span>
                    </li>
                    <li className={this.state.tabs.two}>
                        <span className="step">2</span> <span className="title">Payment</span>

                    </li>
                    <li className={this.state.tabs.three}>
                        <span className="step">3</span> <span className="title">Complete</span>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>
        </div>
    },
    renderAlert(){

        var result=this.state.response.result;
        var status=this.state.response.status;
        var divClass = 'alert-message ' + this.getPaymentColor();
        var text = this.getPaymentText();
        return <div><div className={divClass}>
            {text}
        </div>
            <table className="table table-bordered table-striped">
                <thead>
                <th colSpan="2" style={{textAlign:'center'}}>Payment details</th>
                </thead>
                <tbody>
                <tr>
                    <th style={{width:300}}>Reference Number</th>
                    <td>{status.reference}</td>
                </tr>
                <tr>
                    <th style={{width:300}}>Amount Paid</th>
                    <td>{result.currency} {result.amount}</td>
                </tr>
                <tr>
                    <th style={{width:300}}>Status</th>
                    <td>{status.status}</td>
                </tr>
                <tr>
                    <th style={{width:300}}>Payment Method</th>
                    <td>{status.method}</td>
                </tr>
                </tbody>
            </table>
        </div>
    },
    render: function () {
        var loader = this.state.fetched ? 'none' : 'block';

        return (
            <div id="wallContainer">
                {this.renderSteps()}
                <div className="form-content">
                    <div className="iframe-container">
                        {!this.state.fetched ? <div className="loader-container" style={{display: loader}}>
                                <div className="loader">Updating...</div>
                            </div> : this.renderAlert()

                        }
                    </div>
                </div>
            </div>
        )

    }

});
ReactDOM.render(
    <Container/>,
    document.getElementById('app')
);