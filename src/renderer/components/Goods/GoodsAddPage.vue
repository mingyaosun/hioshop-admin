<template>
  <div class="content-page">
    <div class="content-nav">
      <el-breadcrumb class="breadcrumb" separator="/">
        <el-breadcrumb-item :to="{ name: 'goods' }">商品管理</el-breadcrumb-item>
        <el-breadcrumb-item>{{ infoForm.id ? '编辑商品' : '添加商品' }}</el-breadcrumb-item>
      </el-breadcrumb>
      <div class="operation-nav">
        <el-button type="primary" @click="onSubmitInfo">确定保存</el-button>
        <el-button @click="goBackPage" icon="arrow-left">返回列表</el-button>
      </div>
    </div>
    <div class="content-main">
      <div class="form-table-box">
        <el-form ref="infoForm" :rules="infoRules" :model="infoForm" label-width="120px">
          <el-form-item label="商品分类">
            <el-select class="el-select-class" v-model="cateId"
                       placeholder="选择型号分类">
              <el-option
                  v-for="item in cateOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="商品图片" prop="list_pic_url" v-if="infoForm.list_pic_url"
                        class="image-uploader-diy new-height">
            <img v-if="infoForm.list_pic_url" :src="infoForm.list_pic_url" class="image-show">
            <el-button class="dele-list-pic" type="primary" @click="delePicList">
              <i class="fa fa-trash-o"></i>
            </el-button>
          </el-form-item>
          <el-form-item label="商品图片" prop="list_pic_url" v-if="!infoForm.list_pic_url">
            <el-upload
                name="file"
                class="upload-demo"
                :action="qiniuZone"
                :on-success="handleUploadListSuccess"
                :before-upload="getQiniuToken"
                list-type="picture-card"
                :data="picData"
            >
              <el-button size="small" type="primary">点击上传</el-button>
              <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div>
            </el-upload>
          </el-form-item>
          <el-form-item label="商品轮播图" prop="goods_sn" v-if="infoForm.id">
            <el-upload
                name="file"
                class="upload-demo"
                :action="qiniuZone"
                list-type="picture-card"
                :on-preview="galleryPreview"
                :on-success="handleUploadGallerySuccess"
                :on-remove="galleryRemove"
                :file-list="gallery_list"
                :data="picData"
                :before-upload="galleryBefore"
                :on-error="hasErrorAct"
            >
              <i class="el-icon-plus"></i>
            </el-upload>
            <el-button type="primary" size="small" @click="goodsGalleryEdit">编辑顺序</el-button>

            <el-dialog v-model="dialogVisible" size="tiny">
              <img width="100%" :src="dialogImageUrl" alt="">
            </el-dialog>
          </el-form-item>

          <el-form-item label="商品名称" prop="name">
            <el-input v-model="infoForm.name"></el-input>
          </el-form-item>
          <el-form-item label="商品简介" prop="goods_brief">
            <el-input type="textarea" v-model="infoForm.goods_brief" :rows="3"></el-input>
            <div class="form-tip"></div>
          </el-form-item>
          <el-form-item label="商品单位" prop="goods_unit">
            <el-input v-model="infoForm.goods_unit"></el-input>
            <div class="form-tip">如：件、包、袋</div>
          </el-form-item>
          <el-form-item label="销量" prop="sell_volume">
            <el-input v-model="infoForm.sell_volume"></el-input>
          </el-form-item>
          <el-form-item label="商品详情" prop="goods_desc">
            <div class="edit_container">
              <quill-editor v-model="infoForm.goods_desc"
                            ref="myTextEditor"
                            class="editer"
                            :options="editorOption"
                            @blur="onEditorBlur($event)"
                            @ready="onEditorReady($event)">
              </quill-editor>
            </div>
          </el-form-item>
          <!-- 图片上传组件辅助-->
          <el-form-item class="upload_ad">
            <el-upload
                name="file"
                class="avatar-uploader"
                :action="qiniuZone"
                list-type="picture-card"
                :file-list="detail_list"
                :before-upload="beforeUpload"
                :on-success="handleUploadDetailSuccess"
                :on-preview="handlePreview"
                :data="picData"
            >
            </el-upload>
          </el-form-item>
          <el-form-item label="型号和价格">
            <div>
              <el-select class="el-select-class" v-model="specValue"
                         placeholder="选择型号分类">
                <el-option
                    v-for="item in specOptionsList"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                </el-option>
              </el-select>
            </div>
            <div class="spec-wrap">
              <el-table :data="specData" stripe style="width: 100%">
                <el-table-column prop="goods_sn" label="商品SKU" width="140">
                  <template scope="scope">
                    <el-input @blur="checkSkuOnly(scope.$index, scope.row)" size="mini"
                              v-model="scope.row.goods_sn"
                              placeholder="商品SKU"></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="goods_aka" label="快递单上的简称" width="160">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.goods_name"
                              placeholder="简称"></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="value" label="型号/规格" width="130">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.value" placeholder="如1斤/条"></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="cost" label="成本(元)" width="100">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.cost" placeholder="成本"></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="retail_price" label="零售(元)" width="100">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.retail_price"
                              placeholder="零售"></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="goods_weight" label="重量(KG)" width="100">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.goods_weight"
                              placeholder="重量"></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="goods_number" label="库存" width="100">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.goods_number"
                              placeholder="库存"></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="goods_yl" label="图片" width="100">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.goods_yl" placeholder="1"></el-input>
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="70">
                  <template scope="scope">
                    <el-button
                        size="mini"
                        type="danger"
                        icon="el-icon-delete" circle
                        @click="specDelete(scope.$index, scope.row)">
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
              <el-button class="marginTop20" type="primary" @click="addSpecData">新增型号</el-button>
            </div>
          </el-form-item>
          <el-form-item label="商品评论">
            <div class="spec-wrap">
              <el-table :data="commentData" stripe style="width: 100%">
                <el-table-column prop="goods_aka" label="评论内容" width="300">
                  <template scope="scope">
                    <el-input v-model="scope.row.body" placeholder=""></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="value" label="时间" width="250">
                  <template scope="scope">
                    <el-date-picker v-model="scope.row.time" type="datetime" placeholder="选择日期时间"
                                    default-time="23:59:59">
                    </el-date-picker>
                  </template>
                </el-table-column>
                <el-table-column prop="cost" label="评论者昵称" width="200">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.niname" placeholder=""></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="cost" label="评论者id" width="200">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.publish_id" placeholder=""></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="cost" label="被评论者id" width="200">
                  <template scope="scope">
                    <el-input size="mini" v-model="scope.row.recipient_id"
                              placeholder=""></el-input>
                  </template>
                </el-table-column>
                <el-table-column prop="cost2" label="图片" width="200">
                  <template scope="scope">
                    <el-upload
                        name="file"
                        :action="qiniuZone"
                        list-type="picture-card"
                        :on-preview="function (file) { return galleryPreview1(file, scope.row.id)}"
                        :on-success="function (res, file,fileList) { return handleUploadGallerySuccess1(res, file,fileList,scope)}"
                        :on-remove="function (file,fileList) { return galleryRemove1(file,fileList, scope)}"
                        :file-list="scope.row.list"
                        :data="picData"
                        :before-upload="galleryBefore1"
                        :on-error="hasErrorAct"
                    >
                      <i class="el-icon-plus"></i>
                    </el-upload>
                    <el-dialog v-model="dialogVisible1[scope.row.id]" size="tiny" width="30">
                      <img width="30" :src="dialogImageUrl1[scope.row.id]" alt="">
                    </el-dialog>
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="70">
                  <template scope="scope">
                    <el-button
                        size="mini"
                        type="danger"
                        icon="el-icon-delete" circle
                        @click="commentDelete(scope.$index, scope.row)">
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
              <el-button class="marginTop20" type="primary" @click="addcommentData">新增评论</el-button>
            </div>
          </el-form-item>
          <el-form-item label="属性设置" class="checkbox-wrap">
            <el-checkbox-group v-model="infoForm.is_new" class="checkbox-list">
              <el-checkbox label="新品抢鲜" name="is_new"></el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="选择快递模板">
            <el-select v-model="kdValue" placeholder="请选择快递" @change="kdChange">
              <el-option
                  v-for="item in kdOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="排序" prop="sort_order">
            <el-input-number :mini="1" :max="100" v-model="infoForm.sort_order"></el-input-number>
          </el-form-item>
          <el-form-item label=" ">
            <el-switch active-text="上架" inactive-text="下架" active-value="1" inactive-value="0"
                       v-model="infoForm.is_on_sale"></el-switch>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="onSubmitInfo">确定保存</el-button>
            <el-button @click="goBackPage">返回列表</el-button>
            <el-button type="danger" class="float-right" @click="onCopyGood">复制商品</el-button>
            <!--<el-button @click="testCallBack">回调</el-button>-->
            <!--<el-button @click="getImgUrl">测试</el-button>-->
          </el-form-item>
        </el-form>
      </div>
    </div>
    <el-dialog v-model="dialogVisible" size="tiny">
      <img width="100%" :src="dialogImageUrl" alt="">
    </el-dialog>
  </div>
</template>

<script>
import api from '@/config/api';
import $ from 'jquery'
import {quillEditor} from 'vue-quill-editor'
import ElForm from "../../../../node_modules/element-ui/packages/form/src/form.vue";
import ElFormItem from "../../../../node_modules/element-ui/packages/form/src/form-item.vue"; //调用富文本编辑器
const toolbarOptions = [
  ['bold', 'italic', 'underline', 'strike'],
  ['blockquote', 'code-block'],
  [{'header': 1}, {'header': 2}],
  [{'list': 'ordered'}, {'list': 'bullet'}],
  [{'script': 'sub'}, {'script': 'super'}],
  [{'indent': '-1'}, {'indent': '+1'}],
  [{'direction': 'rtl'}],
  [{'size': ['small', false, 'large', 'huge']}],
  [{'header': [1, 2, 3, 4, 5, 6, false]}],
  [{'font': []}],
  [{'color': []}, {'background': []}],
  [{'align': []}],
  ['clean'],
  ['link', 'image', 'video']
]
export default {
  data() {
    return {
      root: '',
      qiniuZone: '',
      picData: {
        token: ''
      },
      url: '',
      kdOptions: [],
      kdValue: '',
      cateId: '',
      detail_list: [],
      dialogImageUrl: '',
      dialogImageUrl1: [],
      dialogVisible: false,
      dialogVisible1: [],
      options: [],
      cateOptions: [],
      uploaderHeader: {
        'X-Nideshop-Token': localStorage.getItem('token') || '',
      },
      editorOption: {
        modules: {
          toolbar: {
            container: toolbarOptions,  // 工具栏
            handlers: {
              'image': function (value) {
                if (value) {
                  document.querySelector('.avatar-uploader input').click()
                } else {
                  this.quill.format('image', false);
                }
              }
            }
          },
          syntax: {
            highlight: text => hljs.highlightAuto(text).value
          }
        }
      },
      category: [],
      infoForm: {
        name: "",
        list_pic_url: '',
        goods_brief: '',
        goods_desc: '',
        is_on_sale: 0,
        is_new: false,
        // is_index: false,
      },
      infoRules: {
        name: [
          {required: true, message: '请输入名称', trigger: 'blur'},
        ],
        goods_brief: [
          {required: true, message: '请输入简介', trigger: 'blur'},
        ],
        list_pic_url: [
          {required: true, message: '请选择商品图片', trigger: 'blur'},
        ],
      },
      specRules: {
        value: [
          {required: true, message: '请输入型号名', trigger: 'blur'},
        ],
      },
      specData: [{
        goods_sn: '',
        value: '',
        cost: '',
        retail_price: '',
        goods_weight: '',
        goods_number: '',
        goods_yl: '1'
      }],
      commentData: [{
        body: '',
        time: '',
        niname: '',
        id: '',
        publish_id: '',
        recipient_id: '',
        list: []
      }],
      specOptionsList: [],
      specValue: 1,
      selectedSpec: '规格',
      is_has_spec: false,
      is_has_comment: false,
      gallery: {
        goods_id: 0,
      },
      comimg: {
        goods_id: 0,
      },
      gallery_list: [],
      comimg_list: [],
      visible: false,
      hasPost: 0,
      commentImgFirst: [],//第一次添加评论时候的图片数组
    }

  },
  methods: {
    beforeRemove(file, fileList) {
      return this.$confirm(`确定移除 ${file.name}？`);
    },
    checkSkuOnly(index, row) {
      console.log(index);
      console.log(row);
      if (row.goods_sn == '') {
        this.$message({
          type: 'error',
          message: 'SKU不能为空'
        })
        return false;
      }
      this.axios.post('goods/checkSku', {info: row}).then((response) => {
        if (response.data.errno === 100) {
          this.$message({
            type: 'error',
            message: '该SKU已存在！'
          })
        } else {
          this.$message({
            type: 'success',
            message: '该SKU可以用！'
          })
        }
      })
    },
    getSpecData() {
      let id = this.infoForm.id;
      this.axios.post('specification/getGoodsSpec', {id: id}).then((response) => {
        if (response.data.errno === 0) {
          let info = response.data.data;
          this.specData = info.specData;
          this.specValue = info.specValue;

        }
      })
    },
    getcommentData() {
      var _this = this;
      let id = this.infoForm.id;
      this.axios.post('specification/getGoodscomment', {id: id}).then((response) => {
        if (response.data.errno === 0) {
          let info = response.data.data;
          _this.commentData = info.commentData;
          console.log("commentData");
          console.log(_this.commentData);
        }
      })
    },
    addcommentData() {
      let ele = {
        body: '',
        time: '',
        niname: '',
        id: '',
        publish_id: '',
        recipient_id: '',
      }
      this.commentData.push(ele)
    },
    addSpecData() {
      let ele = {
        goods_sn: '',
        value: '',
        cost: '',
        retail_price: '',
        goods_weight: '',
        goods_number: '',
        goods_yl: '1'
      }
      this.specData.push(ele)
    },
    specDelete(index, row) {
      this.specData.splice(index, 1);
    },
    //单条删除评论
    commentDelete(index, row) {
      //TODO 删除图片时候要处理一下存图片的数组
      let that = this;
      if (row.id > 0) {
        let imgList = row.list;
        this.axios.post('goods/deleteComment', {id: row.id}).then((response) => {
          if (response.data.errno === 0) {
            if (!!imgList) {
              for (const argumentsKey in imgList) {
                let imgUrl = imgList[argumentsKey].url;
                let imgId = imgList[argumentsKey].id;
                let file = {
                  id: imgId,
                  url: imgUrl
                }
                //删除评论的照片
                that.galleryRemove1(file);
                that.commentData.splice(index, 1);
                that.commentData[index].list = [];
              }
            }
          }
        })
      } else {
        //TODO 删掉服务器上的照片
        let imgList = row.list;
        if (!!imgList) {
          for (const img of imgList) {
            let file = {
              id: '',
              url: that.url + img.response.key
            }
            //删除评论的照片
            that.galleryRemove1(file);
          }
        }
        that.commentData.splice(index, 1);
      }
    },
    testCallBack() {
      console.log(this.specValue);
    },
    hasErrorAct(err) {
      console.log(err);
    },
    getQiniuToken() {
      let that = this
      this.axios.post('index/getQiniuToken').then((response) => {
        let resInfo = response.data.data;
        that.picData.token = resInfo.token;
        that.url = resInfo.url;
      })
    },
    goodsGalleryEdit() {
      if (this.gallery_list.length > 0) {
        this.$router.push({name: 'goodsgalleryedit', query: {id: this.infoForm.id}})
      } else {
        this.$message({
          type: 'error',
          message: '请先添加商品轮播图'
        })
        return false;
      }

    },
    specChange(value) {
      this.specForm.id = value;
    },
    commentChange(value) {
      this.commentForm.id = value;
    },
    addPrimarySpec() {
      this.is_has_spec = true;
    },
    addPrimarycomment() {
      this.is_has_comment = true;
    },
    getImgUrl() {
      let str = this.infoForm.goods_desc;
      //匹配图片（g表示匹配所有结果i表示区分大小写）
      let imgReg = /<img [^>]*src=['"]([^'"]+)[^>]*>/gi;
      //匹配src属性
      let srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i;
      let arr = str.match(imgReg);
      if (arr == null) {
        return false;
      }
      let data = [];

      for (let i = 0; i < arr.length; i++) {
        let src = arr[i].match(srcReg);
        let jsonData = {};
        jsonData.url = src[1];
        data[i] = jsonData;
      }
      this.detail_list = data;

    },
    submitUpload() {
      this.$refs.upload.submit();
    },
    delePicList() {
      // 删除服务器上的图片
      let key = this.infoForm.list_pic_url;
      let id = this.infoForm.id;
      this.$confirm('确定删除首图？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.axios.post('goods/deleteListPicUrl', {id: id, key: key}).then((response) => {
          if (response.data.errno === 0) {
            this.$message({
              type: 'success',
              message: '删除成功'
            });
            this.infoForm.list_pic_url = '';
          } else {
            this.$message({
              type: 'error',
              message: '删除失败'
            })
          }
        });
      });
    },
    // handleRemove(file, fileList) {
    //     console.log(file, fileList);
    //     let status = file.percentage;
    //     let url = this.url;
    //     let para = '';
    //     if (status == 100) {
    //         para = url + file.response.key;
    //     }
    //     else {
    //         para = file.url;
    //     }
    //     this.axios.post('upload/deleteQiniuGoods', {para: para}).then((response) => {

    //     });
    // },
    handlePreview(file) {
      console.log(file);
      this.dialogImageUrl = file.url;
      this.dialogVisible = true;
    },
    galleryBefore() {
      this.gallery.goods_id = this.infoForm.id;
      this.getQiniuToken();
    },
    galleryBefore1() {
      this.comimg.goods_id = this.infoForm.id;
      this.getQiniuToken();
    },
    galleryRemove(file, fileList) {
      console.log('L>>>>>>>>.');
      let para = {
        id: file.id,
        url: file.url
      }
      this.axios.post('goods/deleteGalleryFile', para).then((response) => {
        if (response.data.errno === 0) {
          this.$message({
            type: 'success',
            message: '删除成功'
          });
        } else {
          this.$message({
            type: 'error',
            message: '删除失败'
          })
        }
      });
    },
    galleryRemove1(file, fileList, scope) {
      // let cid = scope.row.id;
      // let imgList = this.commentData[index].list;
      // let imgNewList = imgList.splice(imgList.indexOf(file.url), 1);
      // console.log('imgList',imgList);
      // this.commentData[index].list = imgNewList;
      // console.log('imgNewList',imgNewList);
      let para = {
        id: file.id,
        url: file.url
      }
      this.axios.post('goods/deletecomimgFile', para).then((response) => {
        if (response.data.errno === 0) {
          this.$message({
            type: 'success',
            message: '删除成功'
          });
        } else {
          this.$message({
            type: 'error',
            message: '删除失败'
          })
        }
      });
    },
    galleryPreview(file) {
      this.dialogImageUrl = file.url;
      this.dialogVisible = true;
    },
    galleryPreview1(file, cid) {
      console.log(file);
      this.dialogImageUrl1[cid] = '';
      this.dialogImageUrl1[cid] = file.url;
      this.dialogVisible1[cid] = true;
    },
    getGalleryList() {
      let goodsId = this.infoForm.id;
      this.axios.post('goods/getGalleryList', {goodsId: goodsId}
      ).then((response) => {
        this.gallery_list = response.data.data.galleryData;
      })
    },
    getGalleryList1(id2) {
      var _this = this;
      let goodsId = this.infoForm.id;
      this.axios.post('goods/getcomimgList', {
            goodsId: goodsId,
            id: id2
          }
      ).then((response) => {
        this.comimg_list[id2] = [];
        this.comimg_list[id2] = response.data.data.galleryData;
        console.log(response.data.data.galleryData);
        _this.commentData.list = response.data.data.galleryData;
        console.log(_this.commentData);
      })
    },
    kdChange(kdValue) {
      this.infoForm.freight_template_id = kdValue;
    },
    timeChange(val) {
      console.log(val);
      // this.infoForm.freight_template_id = kdValue;
    },
    onEditorReady(editor) {
      console.log('editor ready!', editor)
    },
    onEditorFocus(editor) {
      console.log('editor focus!', editor)
    },
    onEditorBlur(editor) {
      console.log('editor blur!', editor)
    },

    beforeUpload() {
      // 显示loading动画
      this.getQiniuToken();
      this.quillUpdateImg = true
    },
    uploadError() {
      // loading动画消失
      this.quillUpdateImg = false
      this.$message.error('图片插入失败')
    },
    goBackPage() {
      this.$router.go(-1);
    },
    //富文本插入网络图片
    onLinkImageUrl() {
      var imageurl = document.querySelector('.url-image-fuzhu input').value
      let quill = this.$refs.myTextEditor.quill
      let length = quill.getSelection().index;
      quill.insertEmbed(length, 'image', imageurl)
      quill.setSelection(length + 1)
    },
    onCopyGood() {
      this.$confirm('确定复制该商品？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.axios.post('goods/copygoods', {id: this.infoForm.id}).then((response) => {
          if (response.data.errno === 0) {
            this.$message({
              type: 'success',
              message: '复制成功!'
            });
//                            this.is_has_spec = false;
//                            this.specData = [];
          }
        })
      });
    },
    onSubmitInfo() {
      this.$refs['infoForm'].validate((valid) => {
        if (valid) {
          if (this.infoForm.index_pic_position > 0) {
            if (this.infoForm.index_pic_url == '' || this.infoForm.index_pic_url == null) {
              this.$message({
                type: 'error',
                message: '请上传特色图片或选择无'
              })
              return false;
            }
          }
          if (this.specData.length == 0) {
            this.$message({
              type: 'error',
              message: '请添加一个规格型号'
            })
            return false;
          }
          for (const ele of this.specData) {
            if (ele.retail_price == '') {
              this.$message({
                type: 'error',
                message: '型号和价格的值不能为空'
              })
              return false;
            }
          }
          // return false;
          this.axios.post('goods/store',
              {
                info: this.infoForm,
                specData: this.specData,
                commentData: this.commentData,
                specValue: this.specValue,
                cateId: this.cateId,
                commentImgFirst: this.commentImgFirst
              }).then((response) => {
            if (response.data.errno === 0) {
              this.$message({
                type: 'success',
                message: '保存成功'
              });
              this.$router.go(-1);
            } else {
              this.$message({
                type: 'error',
                message: '保存失败'
              })
            }
          })
        } else {
          return false;
        }
      });
    },
    handleUploadListSuccess(res) {
      let url = this.url;
      this.infoForm.list_pic_url = url + res.key;
      // this.axios.post('goods/uploadHttpsImage', {url:this.infoForm.list_pic_url}).then((response) => {
      //     let lastUrl = response.data.data;
      //     console.log('lastUrl',lastUrl);
      //     this.infoForm.https_pic_url = lastUrl;
      // })
    },
    handleUploadIndexPicSuccess(res) {
      let url = this.url;
      this.infoForm.index_pic_url = url + res.key;
    },
    handleUploadDetailSuccess(res) {
      let url = this.url;
      let data = url + res.key;
      let quill = this.$refs.myTextEditor.quill
      // 如果上传成功
      // 获取光标所在位置
      let length = quill.getSelection().index;
      // 插入图片  res.info为服务器返回的图片地址
      quill.insertEmbed(length, 'image', data)
      // 调整光标到最后
      quill.setSelection(length + 1)

      // this.$message.error('图片插入失败')
      // loading动画消失
      this.quillUpdateImg = false
    },
    handleUploadGallerySuccess(res) {
      let url = this.url;
      if (res.key != '') {
        let urlData = url + res.key;
        let id = this.infoForm.id;
        let info = {
          url: urlData,
          goods_id: id
        }
        let that = this
        this.axios.post('goods/gallery', info).then((response) => {
          that.getGalleryList();
        })
      }
    },
    handleUploadGallerySuccess1(res, file, fileList, scope) {
      let row = scope.row;
      let cidd = row.id;
      let index = scope.$index;
      let publishId = row.publish_id ? row.publish_id : 0;
      let recipientId = row.recipient_id ? row.recipient_id : 0;
      let that = this;
      let url = that.url;
      that.commentData[index].list = fileList;
      console.log('上传图片成功的回调  ',that.commentData)
      if (res.key != '') {
        let urlData = url + res.key;
        let id = this.infoForm.id;
        let info = {
          url: urlData,
          goods_id: id,
          id: cidd,
          publishId: publishId,
          recipientId: recipientId
        }
      }
    },
    handleUploadImageSuccess(res, file) {
      if (res.errno === 0) {
        switch (res.data.name) {
            //商品图片
          case 'goods_pic':
            this.infoForm.list_pic_url = res.data.fileUrl;
            break;
          case 'goods_detail_pic':
            // res为图片服务器返回的数据
            // 获取富文本组件实例
            let quill = this.$refs.myTextEditor.quill
            // 如果上传成功
            // 获取光标所在位置
            let length = quill.getSelection().index;
            // 插入图片  res.info为服务器返回的图片地址
            quill.insertEmbed(length, 'image', res.data.fileUrl)
            // 调整光标到最后
            quill.setSelection(length + 1)
            // this.$message.error('图片插入失败')
            // loading动画消失
            this.quillUpdateImg = false
            break;
        }
      }
    },
    getInfo() {
      if (this.infoForm.id <= 0) {
        return false
      }
      //加载商品详情
      let that = this
      this.axios.get('goods/info', {
        params: {
          id: that.infoForm.id
        }
      }).then((response) => {
        let resInfo = response.data.data;
        let goodsInfo = resInfo.info;
        // goodsInfo.is_index = goodsInfo.is_index ? true : false;
        goodsInfo.is_new = goodsInfo.is_new ? true : false;
        goodsInfo.is_on_sale = goodsInfo.is_on_sale ? "1" : "0";
        that.infoForm = goodsInfo;
        that.kdValue = goodsInfo.freight_template_id;
        that.cateId = resInfo.category_id;
        that.getImgUrl();
      })
    },
    // 获取所有分类
    getAllCategory() {
      let that = this;
      this.axios.get('goods/getAllCategory', {
        params: {}
      }).then((response) => {
        that.options = response.data.data;
      })
    },
    getAllSpecification() {
      let that = this;
      this.axios.get('goods/getAllSpecification').then((response) => {
        let resInfo = response.data.data;
        that.specOptionsList = resInfo;
      })
    },
    getExpressData() {
      let that = this
      this.axios.get('goods/getExpressData', {
        params: {}
      }).then((response) => {
        let options = response.data.data;
        that.kdOptions = options.kd;
        that.cateOptions = options.cate;
      })
    },
    // summernote 上传图片，返回链接
    sendFile(file) {
    },
    // 初始化 summernote
    initSummerNote() {
      let that = this
      $('#summernote').summernote({
        lang: 'zh-CN',
        placeholder: '请输入商品描述',
        height: 300,
        minHeight: 300,
        maxHeight: 400,
        focus: true,
        // toolbar:[
        //   ['style',['bold','italic','clear']],
        //   ['fontsize',['fontsize']],
        //   ['para',['ul','ol','paragraph']],
        //   ['insert',['picture','link']]
        // ],
        callbacks: {
          onBlur: function (e) {
            console.log(" on blur ");
            console.log($('#summernote').summernote('code'));
            that.infoForm.goods_desc = $('#summernote').summernote('code');
            // that.infoForm.goods_desc = $('#summernote').summernote('code');
          },
          onImageUpload: function (files) {
            console.log("onImageUpLoad...");
            that.sendFile(files[0]);
          }
        }
      }),
          // console.error(that.infoForm.goods_desc);
          $('#summernote').summernote('code', that.infoForm.goods_desc)
    }

  },
  components: {
    ElFormItem,
    ElForm,
    quillEditor
  },
  computed: {
    editor() {
      return this.$refs.myTextEditor.quillEditor
    }
  },
  mounted() {
    this.infoForm.id = this.$route.query.id || 0;
    this.getInfo();
    this.getAllCategory();
    this.getExpressData();
    this.getQiniuToken();
    this.getAllSpecification();
    if (this.infoForm.id > 0) {
      this.getSpecData();
      this.getcommentData();
      this.getGalleryList();
    }
    this.root = api.rootUrl;
    this.qiniuZone = api.qiniu;
  },
}

</script>

<style scoped>
/* .edit_container{ */
/*.avatar-uploader .el-upload {*/
/*display: none;*/
/*}*/
.video-wrap {
  width: 300px;
}

.dialog-header {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 10px;
}

.dialog-header .value {
  width: 150px;
  margin-right: 14px;
}

.input-wrap .el-input {
  width: 200px;
  float: left;
  margin: 0 20px 20px 0;
}

.input-wrap .el-input input {
  background-color: #fff !important;
  color: #233445 !important;
}

.specFormDialig .el-input {
  width: 150px;
  margin-right: 10px;
}

.el-select-class {
  width: 200px;
  margin-right: 20px;
}

.sepc-form {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 10px;
}

.spec-form-wrap {
  margin-top: 0 !important;
}

.add-spec {
  margin-top: 10px;
}

.spec-form-wrap .header {
  display: flex;
  justify-content: flex-start;
}

.spec-form-wrap .header .l {
  width: 200px;
  margin-right: 20px;
}

.spec-form-wrap .header .m {
  width: 200px;
  margin-right: 20px;
}

.spec-form-wrap .header .m {
  width: 200px;
  margin-right: 20px;
}

/*.sepc-form div{*/
/*margin-left: 0;*/
/*}*/

.float-right {
  float: right;
}

.sepc-form .el-input {
  width: 200px;
  margin-right: 20px;
}

.marginTop20 {
  margin-top: 20px;
}

.checkbox-wrap .checkbox-list {
  float: left;
  margin-right: 20px;
}

.upload_ad {
  display: none;
}

.upload_ad .el-upload--picture-card {
  display: none;
}

.ql-container {
  min-height: 200px;
  max-height: 400px;
  overflow-y: auto;
}

.image-uploader-diy {
  /*height: 200px;*/
  position: relative;
}

/*.dele-list-pic {*/
/*position: absolute;*/
/*left: 380px;*/
/*top: 0px;*/
/*}*/

.image-uploader-diy .el-upload {
  border: 1px solid #d9d9d9;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.image-uploader-diy .el-upload:hover {
  border-color: #20a0ff;
}

.image-uploader-diy .image-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 200px;
  height: 200px;
  line-height: 200px;
  text-align: center;
}

.image-uploader-diy .image-show {
  min-width: 105px;
  height: 105px;
  display: block;
}

.image-uploader-diy .new-image-uploader {
  font-size: 28px;
  color: #8c939d;
  width: 165px;
  height: 105px;
  line-height: 105px;
  text-align: center;
}

.image-uploader-diy .new-image-uploader .image-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 165px;
  height: 105px;
  line-height: 105px;
  text-align: center;
}

.image-uploader-diy .new-image-uploader .image-show {
  width: 165px;
  height: 105px;
  display: block;
}

.item-url-image-fuzhu .el-input {
  width: 260px;
}

.hidden {
  display: none;
}
</style>
