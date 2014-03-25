(function(angular){
    "use strict";
    
    var defaultLocationRadius = 2000;
    
    var skeletonData = {
        global: {
            isVerified: false,
            isCombined: false,
            viewMode: 'map',
            filterEntity: null,
            openEntity: {
                id: null,
                type: null
            },
            locationFilters: {
                enabled: null, // circle, address, neighborhood
                circle: {
                    center: {
                        lat: null,
                        lng: null
                    },
                    radius: null
                },
                neighborhood: {
                    center: {
                        lat: null,
                        lng: null
                    },
                    radius: defaultLocationRadius
                },
                address: {
                    text: '',
                    center: {
                        lat: null,
                        lng: null
                    },
                    radius: defaultLocationRadius
                }
            },
            map: {
                zoom: null,
                center: {
                    lat: null,
                    lng: null
                }
            },

            enabled: {
                agent: false,
                space: false,
                event: false
            }
        },
        agent: {
            keyword: '',
            areas: [],
            type: null,
            isVerified: false
        },
        space: {
            keyword: '',
            areas: [],
            types: [],
            acessibilidade: false,
            isVerified: false
        },
        event: {
            keyword: '',
            linguagens: [],
            from: null,
            to: null,
            classificacaoEtaria: [],
            isVerified: false
        }
    };

    var diffFilter = function (input) {
        return _diffFilter(input, skeletonData);
    };

    var isEmpty = function (value) {
        if(typeof value === 'undefined' ||
           value === null) return true;

        if(angular.isObject(value)) {
            if(angular.equals(value, {}) ||
               angular.equals(value, []))
                return true;
        }

        return false;
    };

    var _diffFilter = function (input, skeleton) {
        // returns the difference from the input structure and skeleton
        // don't include nulls

        if(typeof input === 'undefined' || typeof skeleton === 'undefined' || input === skeleton) return;

        if(!angular.isObject(input)|| angular.isArray(skeleton)) {
            return input;
        }

        var output = {};

        angular.forEach(input, function(value, key){
            var currVal = _diffFilter(value, skeleton[key]);

            if(isEmpty(currVal)) return;
            this[key] = currVal;
        }, output);

        return output;
    };

    var deepExtend = function (skeleton, extension) {
        angular.forEach(extension, function(value, key){
            if(angular.isObject(value) && !angular.isArray(value)) {
                deepExtend(skeleton[key], value);
                delete extension[key];
            }
        });
        angular.extend(skeleton, extension);
        return skeleton;
    };

    var app = angular.module('search', ['ng-mapasculturais', 'SearchService', 'SearchMap', 'SearchSpatial', 'rison', 'infinite-scroll', 'ui.date']);

    app.controller('SearchController', ['$scope', '$rootScope', '$location', '$log', '$rison', '$window', '$timeout', 'searchService', function($scope, $rootScope, $location, $log, $rison, $window, $timeout, searchService){
        
        $scope.defaultLocationRadius = defaultLocationRadius;
        
        $rootScope.resetPagination = function(){
            $rootScope.pagination = {
                agent: 1,
                space: 1,
                event: 1
            };
        }
        $rootScope.resetPagination();

        $scope.defaultImageURL = MapasCulturais.defaultAvatarURL;
        $scope.getName = function(valores, id){
            return valores.filter(function(e){if(e.id === id) return true;})[0].name;
        };

        $scope.isSelected = function(array, id){
            return (array.indexOf(id) !== -1);
        };

        $scope.toggleSelection = function(array, id){
            var index = array.indexOf(id);
            if(index !== -1){
                array.splice(index, 1);
            } else {
                array.push(id);
            }
        };


        $scope.switchView = function (mode) {
            $scope.data.global.viewMode = mode;
            if(mode === 'map') {
                //temporary fixes to tim.js' adjustHeader()
                $window.scrollTo(0,1);
                $window.scrollTo(0,0);
            }
        };

        $scope.toggleVerified = function (entity) {

                $scope.data[entity].isVerified = !$scope.data[entity].isVerified;
        };

        $scope.showFilters = function(entity){
            if($scope.data.global.viewMode === 'map')
                return $scope.data.global.enabled[entity];
            else
                return $scope.data.global.filterEntity === entity;
        }

        $scope.hasFilter = function() {
            var ctx = {has: false};
            angular.forEach($scope.data, function(value, key) {
                if(key === 'global') return;
                this.has = this.has || !angular.equals(_diffFilter($scope.data[key], skeletonData[key]), {});
            }, ctx);

            return ctx.has ||
                   $scope.data.global.isVerified ||
                   $scope.data.global.locationFilters.enabled !== null;
        };

        $scope.cleanAllFilters = function () {
            angular.forEach($scope.data, function(value, key) {
                if(key === 'global') return;
                $scope.data[key] = angular.copy(skeletonData[key]);
            });
            $scope.data.global.isVerified = false;
            $scope.data.global.locationFilters = angular.copy(skeletonData.global.locationFilters);
        };

        $scope.cleanLocationFilters = function() {
            $scope.data.global.locationFilters = angular.copy(skeletonData.global.locationFilters);
        };

        $scope.tabClick = function(entity){
            var g = $scope.data.global;
            g.filterEntity = entity;
            if(g.viewMode === 'map'){
                var n = 0;
                for(var e in g.enabled)
                    if(g.enabled[e])
                        n++;

                if(n===0 || n === 1 && !g.enabled[entity]){
                    for(var e in g.enabled)
                        if(e === entity)
                            g.enabled[e] = true;
                        else
                            g.enabled[e] = false;
                }else if(n > 1 && !g.enabled[entity]){
                    g.enabled[entity] = true;
                }
            }
        };

        $scope.parseHash = function(){
            var newValue = $location.hash();
            if(newValue === '') {
                $scope.tabClick('agent');
                return;
            }

            if(newValue !== $rison.stringify(diffFilter($scope.data))){
                $scope.data = deepExtend(angular.copy(skeletonData), $rison.parse(newValue));
                $rootScope.$emit('searchDataChange', $scope.data);
            }
        };

        $scope.dataChange = function(newValue, oldValue){
            if(newValue === undefined) return;
            var serialized = $rison.stringify(diffFilter(newValue));
            $window.$timout = $timeout;
            if($location.hash() !== serialized){
                $timeout.cancel($scope.timer);
                if(oldValue && !angular.equals(oldValue.global.enabled, newValue.global.enabled)) {
                    $location.hash(serialized);
                    $rootScope.$emit('searchDataChange', $scope.data);
                } else {
                    $scope.timer = $timeout(function() {
                        $location.hash(serialized);
                        $rootScope.$emit('searchDataChange', $scope.data);
                    }, 500);
                    $window.dataTimeout = $scope.timer;
                }
            }
        };

        $scope.data = angular.copy(skeletonData);

        $scope.areas = MapasCulturais.taxonomyTerms.area.map(function(el, i){ return {id: i, name: el}; });
        $scope.linguagens = MapasCulturais.taxonomyTerms.linguagem.map(function(el, i){ return {id: i, name: el}; });
        $scope.classificacoes = MapasCulturais.classificacoesEtarias.map(function(el, i){ return {id: i, name: el}; });

        MapasCulturais.entityTypes.agent.push({id:null, name: 'Todos'});
        $scope.types = MapasCulturais.entityTypes;
        $scope.location = $location;

        $rootScope.$on('$locationChangeSuccess', $scope.parseHash);

        if($location.hash() === '') {
            $scope.tabClick('agent');
        } else {
            $scope.parseHash();
        }

        $scope.$watch('data', $scope.dataChange, true);


        $scope.agents = [];
        $scope.spaces = [];
        $scope.events = [];


        $rootScope.$on('searchResultsReady', function(ev, results){
            if($scope.data.global.viewMode !== 'list')
                return;
            
            $rootScope.isPaginating = false;
            
            if(results.paginating){
                console.log( "CONCAT API RESULT" );
                $scope.agents = $scope.agents.concat(results.agent ? results.agent : []);
                $scope.events = $scope.events.concat(results.event ? results.event : []);
                $scope.spaces = $scope.spaces.concat(results.space ? results.space : []);
            }else{
                $scope.agents = results.agent ? results.agent : [];
                $scope.events = results.event ? results.event : [];
                $scope.spaces = results.space ? results.space : [];
            }
        });

        var infiniteScrollTimeout = null;

        $scope.addMore = function(entity){
            if($scope.data.global.viewMode !== 'list')
                return;
            
            if(entity !== $scope.data.global.filterEntity)
                return;
            
            if($rootScope.isPaginating)
                return;
            console.log(entity, $rootScope.pagination[entity]);
            $rootScope.pagination[entity]++;
            console.log(entity, $rootScope.pagination[entity]);
            console.log('getMore');
            $rootScope.$emit('resultPagination', $scope.data);
        };


        $scope.numAgents = 0;
        $scope.numSpaces = 0;
        $scope.numEvents = 0;

        $rootScope.$on('searchCountResultsReady', function(ev, results){
            $scope.numAgents = parseInt(results.agent);
            $scope.numSpaces = parseInt(results.space);
            $scope.numEvents = parseInt(results.event);
        });

        $rootScope.$on('findOneResultReady', function(ev, result){
            $scope.openEntity = result;
        });

        var formatDate = function(date){
            var d = date ? new Date(date + ' 12:00') : new Date();
            return d.toLocaleString('pt-BR',{ day: '2-digit', month:'2-digit', year:'numeric' });
        };

        $scope.dateOptions = {
            dateFormat: 'dd/mm/yy'
        };

        $scope.$watch('data.event.from', function(){
            if(new Date($scope.data.event.from) > new Date($scope.data.event.to))
                $scope.data.event.to = $scope.data.event.from;
        });

        $scope.$watch('data.event.to', function(newValue, oldValue){
            if(new Date($scope.data.event.to) < new Date($scope.data.event.from))
                $scope.data.event.from = $scope.data.event.to;
        });


       $scope.showEventDateFilter = function(){
            var from = $scope.data.event.from,
                to = $scope.data.event.to;

            return from && to && (formatDate(from) !== formatDate() || from !== to );
        };

        $scope.eventDateFilter = function(){
            var from = $scope.data.event.from,
                to = $scope.data.event.to;

            if(from === to)
                return formatDate(from);
            else
                return 'de ' + formatDate(from) + ' a ' + formatDate(to);
        };

        $scope.cleanEventDateFilters = function(){
            $scope.data.event.from = null;
            $scope.data.event.to = null;
        }
    }]);
})(angular);