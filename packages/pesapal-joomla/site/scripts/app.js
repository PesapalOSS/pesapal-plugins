var Iframe = React.createClass({
    getInitialState:function () {
        return{
            preloader:true
        }
    },
    componentDidMount(){
        var reactThis = this;
        $('#MainPopupIframe').on('load', function(){
            $(this).show();
            reactThis.setState({preloader:false});
        });
    },
    render : function () {
        var loader=this.state.preloader ?'block':'none';
        return(
            <div className="iframe-container">
                <div className="loader-container" style={{display:loader}}>
                    <div className="loader">Connecting...</div>
                </div>
            <iframe src={this.props.src} width="100%" height="620px" id="MainPopupIframe" scrolling="no" frameBorder="0">
                <p>Browser unable to load iFrame</p>
            </iframe>
            </div>
        )

    }

});
var Form = React.createClass({

    getInitialState:function () {
        return{
            preloader:false,
            errors:{},
            form:{
                currency:'UGX',
                firstname:'',
                lastname:'',
                email:'',
                amount:0,
                description:''
            }
        }
    },
    validateEmail(email){

        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    },
    validate: function(){
        var errors={};
        var {firstname, lastname,email,amount, description}= this.state.form;
        amount = parseInt(amount);
        if(firstname.trim() === ""){
            Object.assign(errors, {firstname :"Please Enter First Name"})
        }
        if(lastname.trim() === ""){
            Object.assign(errors, {lastname :"Please Enter Last Name"})
        }
        if(description.trim() === "" || description.trim() === "Add description" ){
            Object.assign(errors,{description : "Please add a description"});
        }
        if(amount === 0){
            Object.assign(errors,{amount: "Please Enter valid Amount"})
        }

        if(!this.validateEmail(email)){
            Object.assign(errors,{email: "Enter Valid Email"})
        }
        this.setState({errors:errors});
        if($.isEmptyObject(errors)){
            return true
        }
        return false;
    },
    onUpdate(value,e){
        var form =this.state.form;
        Object.assign(form,{[value]:e.target.value})
        this.setState({form:form})
    },
    saveForm : function(e){
        e.preventDefault();
        if(this.validate()) {
            this.setState({preloader:true});
            var reactThis = this;
            ajaxPostReact('index.php?option=com_pesapal&task=donate&format=raw', this.state.form, function (result) {
                reactThis.props.update(result.responseText,2);
            })
        }

    },
    render : function () {
        // showAdd={this.state.showAdd}
        // payType={this.state.payType}
        var loader=this.state.preloader ?'block':'none';
        var btnTxt=this.props.payType === 'donation' ? 'Donate Now':'Make Payment';
        var colSize =this.props.showAdd === '1' ? 'col-md-6':'col-md-12';
        var addClass =this.props.showAdd === '1' ? 'col-md-6':'hidden';
        var fieldClass =this.props.showAdd === '1' ? 'form-group':'col-md-6 form-group no-padding';
        return(
            <div id="formContainer">
                <div className="loader-container" style={{display:loader}}>
                    <div className="loader">Connecting...</div>
                </div>
                <form onSubmit={this.saveForm}>
                    <fieldset className={colSize}>
                        <legend>Basic Details</legend>
                    <div className={fieldClass}>
                        <label for="firstname">Firstname<span className="errors">*</span>:</label>
                        <input type="text"
                               onChange={(e) => this.onUpdate('firstname',e)}
                               className="form-control" id="firstname"/>
                        <span className="errors">{this.state.errors.firstname}</span>
                    </div>
                        <div className={fieldClass}>
                            <label for="lastname">Lastname<span className="errors">*</span>:</label>
                            <input type="text"
                                   onChange={(e) => this.onUpdate('lastname',e)}
                                   className="form-control" id="lastname"/>
                            <span className="errors">{this.state.errors.lastname}</span>
                        </div>
                        <div className={fieldClass}>
                            <label for="email">Email<span className="errors">*</span>:</label>
                            <input type="email"
                                   onChange={(e) => this.onUpdate('email',e)}
                                   className="form-control" id="email"/>
                            <span className="errors">{this.state.errors.email}</span>
                        </div>
                        <div className={fieldClass}>
                            <label for="mobile">Mobile:</label>
                            <input type="text"
                                   onChange={(e) => this.onUpdate('mobile',e)}
                                   className="form-control" id="mobile"/>
                        </div>
                    </fieldset>

                    <fieldset className={addClass}>
                        <legend>Address</legend>
                        <div className="form-group">
                            <label for="address">Address:</label>
                            <input type="text"
                                   onChange={(e) => this.onUpdate('address',e)}
                                   className="form-control" id="address"/>
                        </div>
                        <div className="form-group">
                            <label for="city">City:</label>
                            <input type="text"
                                   onChange={(e) => this.onUpdate('city',e)}
                                   className="form-control" id="city"/>
                        </div>
                        <div className="form-group">
                            <label for="zipcode">Zip Code:</label>
                            <input type="text"
                                   onChange={(e) => this.onUpdate('zip',e)}
                                   className="form-control" id="zipcode"/>
                        </div>
                        <div className="form-group">
                            <label for="country">Country:</label>
                            <input type="text"
                                   onChange={(e) => this.onUpdate('country',e)}
                                   className="form-control" id="country"/>
                        </div>
                    </fieldset>
                    <fieldset className="col-md-12">
                        <legend>Amount</legend>
                        <div className="form-group">
                            <label for="description">Description<span className="errors">*</span>:</label>
                            <textarea
                                onChange={(e) => this.onUpdate('description',e)}
                                type="text" className="form-control" id="description"></textarea>
                            <span className="errors">{this.state.errors.description}</span>
                        </div>
                        <label for="amount">Amount<span className="errors">*</span>:</label>
                        <div className="form-inline">

                        <div className="form-group">
                            <select className="currency-drop-down"
                                    onChange={(e) => this.onUpdate('currency',e)}
                            >
                                <option>UGX</option>
                                <option>USD</option>
                                <option>KES</option>
                                <option>EUR</option>
                            </select>
                            <input
                                onChange={(e) => this.onUpdate('amount',e)}
                                type="text" className="form-control" id="amount"/>
                        </div>
                        </div>
                        <span className="errors">{this.state.errors.amount}</span>
                    </fieldset>

                    <div className="clearfix"></div>
                    <div className="form-actions">
                        <div className="row">
                            <div className="col-sm-12">
                                <ul className="pager wizard no-margin">

                                    <li className="next">
                                        <button type="submit" className="btn btn-lg txt-color-darken btn-primary"> {btnTxt} </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        )

    }

});

var FormContainer = React.createClass({

    getInitialState : function () {
      return {
          currentTab:1,
          tabs:{
              one:'active',
              two:'pending',
              three:'pending'
          }
      }
    },
    componentWillMount(){
      this.setState({showAdd:$('#address_fields').val(),payType:$('#pay_type').val()})
    },
    renderSteps: function(){
        return <div id="bootstrap-wizard-1" className="col-sm-12">
            <div className="form-bootstrapWizard">
                <ul className="bootstrapWizard form-wizard">
                    <li className={this.state.tabs.one}>
                       <span className="step">1</span> <span className="title">Basic information</span>
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
    update: function(content,tab){
      this.setState({currentTab:tab});
      if(tab===2){
          var tabs={one:'complete',two:'active',three:'pending'};
          this.setState({tabs:tabs});
          this.setState({src:content});
      }
    },
    renderContent : function () {
        switch (this.state.currentTab){
            case 1:
                return <Form
                    showAdd={this.state.showAdd}
                    payType={this.state.payType}
                    update={this.update}
                />
            case 2:
                return <Iframe
                src={this.state.src}/>
        }

    },
    render:function () {
        return <div className="row">
            {this.renderSteps()}
            <div className="form-content">
            {this.renderContent()}
            </div>
        </div>
    }

});
ReactDOM.render(
<FormContainer/>,
    document.getElementById('app')
);