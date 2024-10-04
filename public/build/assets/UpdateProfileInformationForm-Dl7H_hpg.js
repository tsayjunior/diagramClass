import{Q as y,T as x,c as n,d as a,a as o,u as t,b as r,w as m,i as _,x as V,g as c,s as k,f as b,o as u,j as w}from"./app-CIufcYXz.js";import{_ as p}from"./InputError-C3g-1fyd.js";import{a as v,_ as g}from"./TextInput-DhgCy63b.js";import{_ as h}from"./PrimaryButton-RoJUOjMK.js";const S={key:0},N={class:"mt-2 text-sm text-gray-800"},$={class:"mt-2 text-sm font-medium text-green-600"},B={class:"flex items-center gap-4"},E={key:0,class:"text-sm text-gray-600"},I={__name:"UpdateProfileInformationForm",props:{mustVerifyEmail:{type:Boolean},status:{type:String}},setup(d){const l=y().props.auth.user,s=x({name:l.name,email:l.email});return(f,e)=>(u(),n("section",null,[e[6]||(e[6]=a("header",null,[a("h2",{class:"text-lg font-medium text-gray-900"}," Profile Information "),a("p",{class:"mt-1 text-sm text-gray-600"}," Update your account's profile information and email address. ")],-1)),a("form",{onSubmit:e[2]||(e[2]=b(i=>t(s).patch(f.route("profile.update")),["prevent"])),class:"mt-6 space-y-6"},[a("div",null,[o(g,{for:"name",value:"Name"}),o(v,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:t(s).name,"onUpdate:modelValue":e[0]||(e[0]=i=>t(s).name=i),required:"",autofocus:"",autocomplete:"name"},null,8,["modelValue"]),o(p,{class:"mt-2",message:t(s).errors.name},null,8,["message"])]),a("div",null,[o(g,{for:"email",value:"Email"}),o(v,{id:"email",type:"email",class:"mt-1 block w-full",modelValue:t(s).email,"onUpdate:modelValue":e[1]||(e[1]=i=>t(s).email=i),required:"",autocomplete:"username"},null,8,["modelValue"]),o(p,{class:"mt-2",message:t(s).errors.email},null,8,["message"])]),d.mustVerifyEmail&&t(l).email_verified_at===null?(u(),n("div",S,[a("p",N,[e[4]||(e[4]=r(" Your email address is unverified. ")),o(t(w),{href:f.route("verification.send"),method:"post",as:"button",class:"rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"},{default:m(()=>e[3]||(e[3]=[r(" Click here to re-send the verification email. ")])),_:1},8,["href"])]),_(a("div",$," A new verification link has been sent to your email address. ",512),[[V,d.status==="verification-link-sent"]])])):c("",!0),a("div",B,[o(h,{disabled:t(s).processing},{default:m(()=>e[5]||(e[5]=[r("Save")])),_:1},8,["disabled"]),o(k,{"enter-active-class":"transition ease-in-out","enter-from-class":"opacity-0","leave-active-class":"transition ease-in-out","leave-to-class":"opacity-0"},{default:m(()=>[t(s).recentlySuccessful?(u(),n("p",E," Saved. ")):c("",!0)]),_:1})])],32)]))}};export{I as default};
